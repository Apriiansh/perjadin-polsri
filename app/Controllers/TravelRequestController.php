<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\TravelExpenseCalculator;
use CodeIgniter\HTTP\ResponseInterface;

class TravelRequestController extends BaseController
{
    protected $travelRequestModel;
    protected $travelMemberModel;
    protected $travelExpenseModel;
    protected $employeeModel;
    protected $signatoryModel;
    protected $tariffModel;

    public function __construct()
    {
        $this->travelRequestModel = model('TravelRequestModel');
        $this->travelMemberModel  = model('TravelMemberModel');
        $this->travelExpenseModel = model('TravelExpenseModel');
        $this->employeeModel      = model('EmployeeModel');
        $this->signatoryModel     = model('SignatoriesModel');
        $this->tariffModel        = model('TariffModel');
    }

    /**
     * Helper: Check if logged-in user is admin or superadmin (Kepegawaian/Keuangan)
     */
    private function isStaff(): bool
    {
        $groups = auth()->user()?->getGroups() ?? [];
        return in_array('admin', $groups) || in_array('superadmin', $groups);
    }

    /**
     * Helper: Get the employee record linked to current user
     */
    private function getCurrentEmployee(): ?array
    {
        return $this->employeeModel->where('user_id', auth()->id())->first();
    }

    /**
     * List travel requests — role-aware
     */
    public function index(): string|ResponseInterface
    {
        if ($this->isStaff()) {
            $travelRequests = $this->travelRequestModel->getAllRequests();
        } else {
            // Dosen only sees requests where they are a member
            $employee = $this->getCurrentEmployee();
            if (!$employee) {
                return view('travel/index', [
                    'title'          => 'Pengajuan Perdin',
                    'travelRequests' => [],
                    'isStaff'        => false,
                ]);
            }

            // Find travel_request IDs where this employee is a member
            $memberRows = $this->travelMemberModel->where('employee_id', $employee['id'])->findAll();
            $requestIds = array_unique(array_column($memberRows, 'travel_request_id'));

            $travelRequests = !empty($requestIds)
                ? $this->travelRequestModel->whereIn('id', $requestIds)->orderBy('created_at', 'DESC')->findAll()
                : [];
        }

        // Attach members to each request
        foreach ($travelRequests as &$req) {
            $req->members = $this->travelMemberModel->getByRequestWithEmployee($req->id);
        }

        return view('travel/index', [
            'title'          => 'Pengajuan Perdin',
            'travelRequests' => $travelRequests,
            'isStaff'        => $this->isStaff(),
        ]);
    }

    /**
     * List active travel requests
     */
    public function active(): string|ResponseInterface
    {
        if ($this->isStaff()) {
            $travelRequests = $this->travelRequestModel->where('status', 'active')->orderBy('created_at', 'DESC')->findAll();
        } elseif (auth()->user()->inGroup('verificator')) {
            // Verificator sees all active requests
            $travelRequests = $this->travelRequestModel->where('status', 'active')->orderBy('created_at', 'DESC')->findAll();
        } else {
            // Dosen only sees active requests where they are a member
            $employee = $this->getCurrentEmployee();
            if (!$employee) {
                return view('travel/index', [
                    'title'          => 'Perjalanan Dinas Aktif',
                    'travelRequests' => [],
                    'isStaff'        => false,
                ]);
            }

            // Find travel_request IDs where this employee is a member
            $memberRows = $this->travelMemberModel->where('employee_id', $employee['id'])->findAll();
            $requestIds = array_unique(array_column($memberRows, 'travel_request_id'));

            $travelRequests = !empty($requestIds)
                ? $this->travelRequestModel->whereIn('id', $requestIds)->where('status', 'active')->orderBy('created_at', 'DESC')->findAll()
                : [];
        }

        $isVerifOnly = auth()->user()->inGroup('verificator') && !$this->isStaff();
        $title = $isVerifOnly ? 'Verifikasi Perdin' : 'Perjalanan Dinas Aktif';

        // Attach members and documentation stats to each request
        $completenessModel = new \App\Models\TravelCompletenessModel();
        foreach ($travelRequests as &$req) {
            $req->members = $this->travelMemberModel->getByRequestWithEmployee($req->id);

            $items = $completenessModel->where('travel_request_id', $req->id)->findAll();
            $req->total_docs = count($items);
            $req->uploaded_docs = count(array_filter($items, fn($i) => $i->status === 'uploaded'));
            $req->verified_docs = count(array_filter($items, fn($i) => $i->status === 'verified'));
        }

        return view('travel/index', [
            'title'          => $title,
            'travelRequests' => $travelRequests,
            'isStaff'        => $this->isStaff(),
        ]);
    }

    /**
     * Create form — only Kepegawaian / Keuangan
     */
    public function create(): string|ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel')->with('error', 'Anda tidak memiliki akses untuk membuat pengajuan.');
        }

        return view('travel/create', [
            'title'     => 'Input Data Perjalanan Dinas',
            'employees' => $this->employeeModel->findAll(),
            'tariffs'   => $this->tariffModel->findAll(),
        ]);
    }

    /**
     * Store new travel request
     */
    public function store(): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel')->with('error', 'Anda tidak memiliki akses.');
        }

        $dataRequest = [
            'no_surat_tugas'              => $this->request->getPost('no_surat_tugas'),
            'tgl_surat_tugas'             => $this->request->getPost('tgl_surat_tugas'),
            'nomor_surat_rujukan'         => $this->request->getPost('nomor_surat_rujukan'),
            'tgl_surat_rujukan'           => $this->request->getPost('tgl_surat_rujukan'),
            'instansi_pengirim_rujukan'   => $this->request->getPost('instansi_pengirim_rujukan'),
            'perihal_surat_rujukan'       => $this->request->getPost('perihal_surat_rujukan'),
            'destination_province'        => $this->request->getPost('destination_province'),
            'destination_city'            => $this->request->getPost('destination_city'),
            'lokasi'                      => $this->request->getPost('lokasi') ?: null,
            'departure_place'             => $this->request->getPost('departure_place') ?: null,
            'budget_burden_by'            => $this->request->getPost('budget_burden_by'),
            'tahun_anggaran'              => $this->request->getPost('tahun_anggaran'),
            'departure_date'              => $this->request->getPost('departure_date'),
            'return_date'                 => $this->request->getPost('return_date'),
            'status'                      => 'draft',
            'created_by'                  => auth()->id(),
        ];

        // Auto-calculate duration_days from departure_date & return_date
        if (!empty($dataRequest['departure_date']) && !empty($dataRequest['return_date'])) {
            $start = new \DateTime($dataRequest['departure_date']);
            $end   = new \DateTime($dataRequest['return_date']);
            $dataRequest['duration_days'] = (int) $start->diff($end)->days + 1;
        }

        // Handle file upload (lampiran ST)
        $lampiran = $this->request->getFile('lampiran');
        if ($lampiran && $lampiran->isValid() && !$lampiran->hasMoved()) {
            $newName = $lampiran->getRandomName();
            $lampiran->move(WRITEPATH . 'uploads/travel', $newName);
            $dataRequest['lampiran_path']          = 'travel/' . $newName;
            $dataRequest['lampiran_original_name']  = $lampiran->getClientName();
        }

        $result = $this->travelRequestModel->insert($dataRequest);
        if ($result === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat pengajuan: ' . implode(', ', $this->travelRequestModel->errors()));
        }

        $requestId = $this->travelRequestModel->insertID();

        // Process members: create travel_members first, then calculate expenses
        $memberIds = $this->request->getPost('members');
        $memberGolongan = $this->request->getPost('member_golongan') ?? [];
        $calculator = new TravelExpenseCalculator();
        $totalBudgetAll = 0;
        $errors = [];

        if (!empty($memberIds) && is_array($memberIds)) {
            foreach ($memberIds as $empId) {
                $emp = $this->employeeModel->find($empId);
                if (!$emp) continue;

                // Create travel_member row with golongan snapshot from form
                $kodeGol = $memberGolongan[$empId] ?? null;
                $namaGol = $kodeGol ? $this->getNamaGolongan($kodeGol) : null;

                $this->travelMemberModel->insert([
                    'travel_request_id' => $requestId,
                    'employee_id'       => $empId,
                    'kode_golongan'     => $kodeGol,
                    'nama_golongan'     => $namaGol,
                ]);
                $memberId = $this->travelMemberModel->insertID();

                // Use kode_golongan from form for tingkat biaya, fallback to employee API data
                $golForTingkat = $kodeGol ?: ($emp['pangkat_golongan'] ?? '');
                $tingkatBiaya = $this->getTingkatBiayaFromGolongan($golForTingkat);

                $biaya = [
                    'uang_harian'       => 0,
                    'uang_representasi' => 0,
                    'penginapan'        => 0,
                    'tiket'             => 0,
                    'transport_darat'   => 0,
                    'transport_lokal'   => 0,
                    'total_biaya'       => 0,
                    'tariff_id'         => null,
                ];

                try {
                    $biaya = $calculator->calculate(
                        $dataRequest['destination_province'],
                        $dataRequest['destination_city'] ?? null,
                        $tingkatBiaya,
                        $dataRequest['duration_days'] ?? 0
                    );
                } catch (\Exception $e) {
                    $errors[] = $emp['name'] . ' - ' . $e->getMessage();
                }

                // Saat input ST, hanya uang_harian & uang_representasi yang dihitung.
                // Penginapan, tiket, transport diisi saat kelengkapan.
                $subtotal = $biaya['uang_harian'] + $biaya['uang_representasi'];

                $this->travelExpenseModel->insert([
                    'travel_member_id'  => $memberId,
                    'uang_harian'       => $biaya['uang_harian'],
                    'uang_representasi' => $biaya['uang_representasi'],
                    'penginapan'        => 0,
                    'tiket'             => 0,
                    'transport_darat'   => 0,
                    'transport_lokal'   => 0,
                    'total_biaya'       => $subtotal,
                    'tariff_id'         => $biaya['tariff_id'],
                ]);
                $totalBudgetAll += $subtotal;
            }
        }

        $this->travelRequestModel->update($requestId, ['total_budget' => $totalBudgetAll]);

        $successMsg = 'Pengajuan perjalanan dinas berhasil disimpan. Silahkan lengkapi data Perjalanan Dinas di halaman Detail.';

        if (!empty($errors)) {
            return redirect()->to('/travel/' . $requestId)->with('warning', $successMsg . '<br>Tetapi ada tarif yang belum diatur:<br>' . implode('<br>', $errors));
        }

        return redirect()->to('/travel/' . $requestId)->with('success', $successMsg);
    }

    /**
     * Show detail — all roles can view (Dosen filtered by membership)
     */
    public function show(int $id): string|ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);

        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        // Dosen can only view requests where they are a member
        // Staff (Admin/Superadmin) and Verificators can view any request
        if (!$this->isStaff() && !auth()->user()->inGroup('verificator')) {
            $employee = $this->getCurrentEmployee();
            $isMember = $employee
                ? $this->travelMemberModel->where('travel_request_id', $id)->where('employee_id', $employee['id'])->first()
                : null;
            if (!$isMember) {
                return redirect()->to('/travel')->with('error', 'Anda tidak memiliki akses ke data ini.');
            }
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);

        // Fetch itemized expenses (Phase 8)
        $expenseItemModel = new \App\Models\TravelExpenseItemModel();
        foreach ($members as &$member) {
            $member->expense_items = $expenseItemModel->where('travel_member_id', $member->travel_member_id)->findAll();
        }

        // Get completeness items
        $completenessModel = model('TravelCompletenessModel');
        $fileModel = model('TravelCompletenessFileModel');
        $completeness = $completenessModel->getByRequestId($id);

        foreach ($completeness as $item) {
            $item->files = $fileModel->getByCompletenessId($item->id);
        }

        return view('travel/show', [
            'title'          => 'Detail Perjalanan Dinas',
            'travelRequest'  => $travelRequest,
            'members'        => $members,
            'completeness'   => $completeness,
            'signatories'    => $this->signatoryModel->getAllWithEmployee(),
            'isStaff'        => $this->isStaff(),
        ]);
    }

    /**
     * Edit form — only Kepegawaian / Keuangan
     */
    public function edit(int $id): string|ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel')->with('error', 'Anda tidak memiliki akses untuk mengubah pengajuan.');
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        return view('travel/edit', [
            'title'          => 'Edit Data Perjalanan Dinas',
            'travelRequest'  => $travelRequest,
            'travelMembers'  => $this->travelMemberModel->where('travel_request_id', $id)->findAll(),
            'employees'      => $this->employeeModel->findAll(),
            'tariffs'        => $this->tariffModel->findAll(),
        ]);
    }

    /**
     * Update travel request
     */
    public function update(int $id): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel')->with('error', 'Anda tidak memiliki akses.');
        }

        $currentRequest = $this->travelRequestModel->find($id);
        if (!$currentRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'no_surat_tugas'              => $this->request->getPost('no_surat_tugas'),
            'tgl_surat_tugas'             => $this->request->getPost('tgl_surat_tugas'),
            'nomor_surat_rujukan'         => $this->request->getPost('nomor_surat_rujukan'),
            'tgl_surat_rujukan'           => $this->request->getPost('tgl_surat_rujukan'),
            'instansi_pengirim_rujukan'   => $this->request->getPost('instansi_pengirim_rujukan'),
            'perihal_surat_rujukan'       => $this->request->getPost('perihal_surat_rujukan'),
            'destination_province'        => $this->request->getPost('destination_province'),
            'destination_city'            => $this->request->getPost('destination_city'),
            'lokasi'                      => $this->request->getPost('lokasi') ?: null,
            'departure_place'             => $this->request->getPost('departure_place') ?: null,
            'budget_burden_by'            => $this->request->getPost('budget_burden_by'),
            'tahun_anggaran'              => $this->request->getPost('tahun_anggaran'),
            'departure_date'              => $this->request->getPost('departure_date'),
            'return_date'                 => $this->request->getPost('return_date'),
        ];

        // Auto-calculate duration_days from departure_date & return_date
        if (!empty($data['departure_date']) && !empty($data['return_date'])) {
            $start = new \DateTime($data['departure_date']);
            $end   = new \DateTime($data['return_date']);
            $data['duration_days'] = (int) $start->diff($end)->days + 1;
        }

        // Handle file upload (lampiran ST)
        $lampiran = $this->request->getFile('lampiran');
        if ($lampiran && $lampiran->isValid() && !$lampiran->hasMoved()) {
            // Delete old file if exists
            if (!empty($currentRequest->lampiran_path)) {
                $oldPath = WRITEPATH . 'uploads/' . $currentRequest->lampiran_path;
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
            $newName = $lampiran->getRandomName();
            $lampiran->move(WRITEPATH . 'uploads/travel', $newName);
            $data['lampiran_path']          = 'travel/' . $newName;
            $data['lampiran_original_name'] = $lampiran->getClientName();
        }

        // Status tetap draft, perubahan status dilakukan di halaman detail (Lengkapi Data)

        $result = $this->travelRequestModel->update($id, $data);
        if ($result === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengubah pengajuan: ' . implode(', ', $this->travelRequestModel->errors()));
        }

        // Delete old members (cascade deletes travel_expenses via FK)
        $this->travelMemberModel->where('travel_request_id', $id)->delete();

        // Re-create members and recalculate expenses
        $memberIds = $this->request->getPost('members');
        $memberGolongan = $this->request->getPost('member_golongan') ?? [];
        $calculator = new TravelExpenseCalculator();
        $totalBudgetAll = 0;
        $errors = [];

        if (!empty($memberIds) && is_array($memberIds)) {
            foreach ($memberIds as $empId) {
                $emp = $this->employeeModel->find($empId);
                if (!$emp) continue;

                $kodeGol = $memberGolongan[$empId] ?? null;
                $namaGol = $kodeGol ? $this->getNamaGolongan($kodeGol) : null;

                $this->travelMemberModel->insert([
                    'travel_request_id' => $id,
                    'employee_id'       => $empId,
                    'kode_golongan'     => $kodeGol,
                    'nama_golongan'     => $namaGol,
                ]);
                $memberId = $this->travelMemberModel->insertID();

                $golForTingkat = $kodeGol ?: ($emp['pangkat_golongan'] ?? '');
                $tingkatBiaya = $this->getTingkatBiayaFromGolongan($golForTingkat);

                $biaya = [
                    'uang_harian'       => 0,
                    'uang_representasi' => 0,
                    'penginapan'        => 0,
                    'tiket'             => 0,
                    'transport_darat'   => 0,
                    'transport_lokal'   => 0,
                    'total_biaya'       => 0,
                    'tariff_id'         => null,
                ];

                try {
                    $biaya = $calculator->calculate(
                        $data['destination_province'],
                        $data['destination_city'] ?? null,
                        $tingkatBiaya,
                        $data['duration_days'] ?? 0
                    );
                } catch (\Exception $e) {
                    $errors[] = $emp['name'] . ' - ' . $e->getMessage();
                }

                // Saat input ST, hanya uang_harian & uang_representasi yang dihitung.
                // Penginapan, tiket, transport diisi saat kelengkapan.
                $subtotal = $biaya['uang_harian'] + $biaya['uang_representasi'];

                $this->travelExpenseModel->insert([
                    'travel_member_id'  => $memberId,
                    'uang_harian'       => $biaya['uang_harian'],
                    'uang_representasi' => $biaya['uang_representasi'],
                    'penginapan'        => 0,
                    'tiket'             => 0,
                    'transport_darat'   => 0,
                    'transport_lokal'   => 0,
                    'total_biaya'       => $subtotal,
                    'tariff_id'         => $biaya['tariff_id'],
                ]);
                $totalBudgetAll += $subtotal;
            }
        }

        $this->travelRequestModel->update($id, ['total_budget' => $totalBudgetAll]);

        $successMsg = 'Data berhasil diubah. Silahkan lengkapi data Perjalanan Dinas di halaman Detail.';

        if (!empty($errors)) {
            return redirect()->to('/travel/' . $id)->with('warning', $successMsg . '<br>Tetapi ada tarif yang belum diatur:<br>' . implode('<br>', $errors));
        }

        return redirect()->to('/travel/' . $id)->with('success', $successMsg);
    }

    /**
     * Delete travel request — staff only (FK cascade handles members & expenses)
     */
    public function destroy(int $id): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel')->with('error', 'Anda tidak memiliki akses.');
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        // Delete uploaded lampiran if exists
        if (!empty($travelRequest->lampiran_path)) {
            $filePath = WRITEPATH . 'uploads/' . $travelRequest->lampiran_path;
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        $this->travelRequestModel->delete($id);

        return redirect()->to('/travel')->with('success', 'Pengajuan perjalanan dinas berhasil dihapus.');
    }

    /**
     * Activate draft — staff only
     */
    public function submit(int $id): ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        if ($travelRequest->status !== 'draft') {
            return redirect()->to('/travel/' . $id)->with('error', 'Hanya pengajuan berstatus draft yang dapat diaktifkan.');
        }

        $this->travelRequestModel->update($id, ['status' => 'active']);

        return redirect()->to('/travel/' . $id)->with('success', 'Pengajuan berhasil diaktifkan.');
    }

    /**
     * Cancel active back to draft — staff only
     */
    public function cancel(int $id): ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        if ($travelRequest->status !== 'active') {
            return redirect()->to('/travel/' . $id)->with('error', 'Hanya pengajuan berstatus aktif yang dapat dikembalikan ke draft.');
        }

        $this->travelRequestModel->update($id, ['status' => 'draft']);

        return redirect()->to('/travel/' . $id)->with('success', 'Pengajuan berhasil dikembalikan menjadi draft.');
    }

    /**
     * API: Search employees with filter
     */
    public function getEmployees(): ResponseInterface
    {
        $search = $this->request->getGet('q');
        $golongan = $this->request->getGet('golongan');
        $jurusan = $this->request->getGet('jurusan');

        $builder = $this->employeeModel->builder();

        if (!empty($search)) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('nip', $search)
                ->groupEnd();
        }

        if (!empty($golongan)) {
            $builder->where('pangkat_golongan', $golongan);
        }

        if (!empty($jurusan)) {
            $builder->where('nama_jurusan', $jurusan);
        }

        $employees = $builder->limit(50)->get()->getResultArray();

        return $this->response->setJSON($employees);
    }

    /**
     * API: Real-time tariff check
     */
    public function checkTariff(): ResponseInterface
    {
        $province = $this->request->getPost('province');
        $city = $this->request->getPost('city');
        $memberIds = $this->request->getPost('members');

        if (is_string($memberIds)) {
            $memberIds = json_decode($memberIds, true);
        }

        if (empty($province) || empty($memberIds)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid parameters']);
        }

        $calculator = new TravelExpenseCalculator();
        $missing = [];
        $success = [];

        foreach ($memberIds as $memberId) {
            $emp = $this->employeeModel->find($memberId);
            if (!$emp) continue;

            $tingkatBiaya = $this->getTingkatBiayaFromGolongan($emp['pangkat_golongan'] ?? '');

            try {
                $calculator->calculate($province, $city, $tingkatBiaya, 1);
                $success[] = ['name' => $emp['name'], 'tingkat_biaya' => $tingkatBiaya];
            } catch (\Exception $e) {
                $missing[] = ['name' => $emp['name'], 'tingkat_biaya' => $tingkatBiaya, 'error' => $e->getMessage()];
            }
        }

        return $this->response->setJSON([
            'status'          => 'success',
            'missing_tariffs' => $missing,
            'success_tariffs' => $success,
        ]);
    }

    /**
     * Map kode_golongan (e.g. IV/a, III/d) to nama_golongan
     */
    private function getNamaGolongan(string $kode): string
    {
        $map = [
            'IV/e'  => 'Pembina Utama',
            'IV/d'  => 'Pembina Utama Madya',
            'IV/c'  => 'Pembina Utama Muda',
            'IV/b'  => 'Pembina TK. I',
            'IV/a'  => 'Pembina',
            'III/d' => 'Penata TK. I',
            'III/c' => 'Penata',
            'III/b' => 'Penata Muda TK. 1',
            'III/a' => 'Penata Muda',
            'II/d'  => 'Pengatur TK. I',
            'II/c'  => 'Pengatur',
            'II/b'  => 'Pengatur Muda TK. 1',
            'II/a'  => 'Pengatur Muda',
            'I/d'   => 'Juru TK. I',
            'I/c'   => 'Juru',
            'I/b'   => 'Juru Muda TK. 1',
            'I/a'   => 'Juru Muda',
        ];
        return $map[$kode] ?? '';
    }

    /**
     * Map Pangkat/Golongan to Tingkat Biaya Perjadin
     */
    private function getTingkatBiayaFromGolongan(string $golongan): string
    {
        $golongan = strtoupper(trim($golongan));

        if (strpos($golongan, 'IV') !== false) {
            return 'A';
        } elseif (strpos($golongan, 'III') !== false) {
            return 'B';
        } elseif (strpos($golongan, 'II') !== false && strpos($golongan, 'III') === false) {
            return 'C';
        } elseif (strpos($golongan, 'I') !== false && strpos($golongan, 'II') === false && strpos($golongan, 'III') === false && strpos($golongan, 'IV') === false) {
            return 'D';
        }

        return 'C';
    }

    // -------------------------------------------------------------------------
    // DOCUMENT GENERATION
    // -------------------------------------------------------------------------

    /**
     * Download lampiran Surat Tugas (uploaded file)
     */
    public function downloadLampiran(int $id): ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        if (!$this->isStaff()) {
            $emp = $this->getCurrentEmployee();
            $isMember = $emp
                ? $this->travelMemberModel->where('travel_request_id', $id)->where('employee_id', $emp['id'])->first()
                : null;
            if (!$isMember) {
                return redirect()->to('/travel')->with('error', 'Akses ditolak.');
            }
        }

        if (empty($travelRequest->lampiran_path)) {
            return redirect()->to('/travel/' . $id)->with('error', 'Lampiran surat tugas belum diunggah.');
        }

        $filePath = WRITEPATH . 'uploads/' . $travelRequest->lampiran_path;
        if (!is_file($filePath)) {
            return redirect()->to('/travel/' . $id)->with('error', 'File lampiran tidak ditemukan.');
        }

        $originalName = $travelRequest->lampiran_original_name ?: basename($filePath);

        return $this->response->download($filePath, null)->setFileName($originalName);
    }

    /**
     * Download specific documentation file (Phase 12)
     */
    public function downloadFile(int $fileId): ResponseInterface
    {
        $fileModel = model('TravelCompletenessFileModel');
        $file = $fileModel->find($fileId);
        if (!$file) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $filePath = WRITEPATH . 'uploads/' . $file->file_path;
        if (!is_file($filePath)) {
            return redirect()->back()->with('error', 'File fisik tidak ditemukan.');
        }

        return $this->response->download($filePath, null)->setFileName($file->original_name);
    }

    /**
     * Download SPD (.docx)
     * PPK can be passed via ?ppk_id query param, or auto-resolved from signatories.
     */
    public function downloadSpd(int $id): ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        if (!$this->isStaff()) {
            $emp = $this->getCurrentEmployee();
            $isMember = $emp
                ? $this->travelMemberModel->where('travel_request_id', $id)->where('employee_id', $emp['id'])->first()
                : null;
            if (!$isMember) {
                return redirect()->to('/travel')->with('error', 'Akses ditolak.');
            }
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);

        // Resolve PPK: prioritize ppk_id from travel_requests, then query param, then fallback to active PPK
        $ppkId = $travelRequest->ppk_id ?: $this->request->getGet('ppk_id');
        $ppk = $this->resolveSignatory((string) $ppkId);

        if (!$ppk) {
            $ppkSig = $this->signatoryModel
                ->like('jabatan', 'PPK')
                ->where('is_active', 1)
                ->first();
            if ($ppkSig) {
                $ppk = $this->resolveSignatory((string) $ppkSig->id);
            }
        }

        (new \App\Libraries\Templates\SppdTemplate())->generate($travelRequest, $members, $ppk);
        exit; // generate() streams output
    }

    /**
     * Generate and download Surat Pernyataan.
     */
    public function downloadStatement(int $id): ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        $isStaff = $this->isStaff();
        $emp = $this->getCurrentEmployee();
        $specificMemberId = $this->request->getGet('member_id');

        if (!$isStaff) {
            $isMember = $emp
                ? $this->travelMemberModel->where('travel_request_id', $id)->where('employee_id', $emp['id'])->first()
                : null;
            if (!$isMember) {
                return redirect()->to('/travel')->with('error', 'Akses ditolak.');
            }
            // Lecturer can only see their own
            $specificMemberId = $isMember->id;
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);

        // Resolve PPK
        $ppkId = $travelRequest->ppk_id;
        $ppk = $this->resolveSignatory((string) $ppkId);

        if (!$ppk) {
            $ppkSig = $this->signatoryModel
                ->like('jabatan', 'PPK')
                ->where('is_active', 1)
                ->first();
            if ($ppkSig) {
                $ppk = $this->resolveSignatory((string) $ppkSig->id);
            }
        }

        (new \App\Libraries\Templates\SuratPernyataanTemplate())->generate($travelRequest, $members, $ppk, $specificMemberId);
        exit;
    }

    /**
     * Generate and download Daftar Kontrol Pembayaran (Excel).
     */
    public function downloadControlList(int $id): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel')->with('error', 'Akses ditolak.');
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);

        // Resolve BPP
        $bppId = $travelRequest->bpp_id;
        $bpp = $this->resolveSignatory((string) $bppId);

        if (!$bpp) {
            $bppSig = $this->signatoryModel
                ->like('jabatan', 'Bendahara Pengeluaran Pembantu')
                ->where('is_active', 1)
                ->first();
            if ($bppSig) {
                $bpp = $this->resolveSignatory((string) $bppSig->id);
            }
        }

        (new \App\Libraries\Templates\DaftarKontrolTemplate())->generate($travelRequest, $members, $bpp);
        exit;
    }

    /**
     * Resolve a signatory by ID and attach employee name/nip.
     */
    private function resolveSignatory(?string $signatoryId): ?object
    {
        if (!$signatoryId) {
            return null;
        }
        $sig = $this->signatoryModel->find($signatoryId);
        if (!$sig) {
            return null;
        }
        $emp = $this->employeeModel->find($sig->employee_id);
        $sig->employee_name = $emp['name'] ?? 'Unknown';
        $sig->nip           = $emp['nip'] ?? '';
        return $sig;
    }
}
