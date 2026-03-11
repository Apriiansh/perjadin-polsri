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
            'tgl_mulai'                   => $this->request->getPost('tgl_mulai'),
            'tgl_selesai'                 => $this->request->getPost('tgl_selesai'),
            'status'                      => 'draft',
            'created_by'                  => auth()->id(),
        ];

        // Auto-calculate duration_days from tgl_mulai & tgl_selesai
        if (!empty($dataRequest['tgl_mulai']) && !empty($dataRequest['tgl_selesai'])) {
            $start = new \DateTime($dataRequest['tgl_mulai']);
            $end   = new \DateTime($dataRequest['tgl_selesai']);
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
        $calculator = new TravelExpenseCalculator();
        $totalBudgetAll = 0;
        $errors = [];

        if (!empty($memberIds) && is_array($memberIds)) {
            foreach ($memberIds as $empId) {
                $emp = $this->employeeModel->find($empId);
                if (!$emp) continue;

                // Create travel_member row with golongan snapshot
                $this->travelMemberModel->insert([
                    'travel_request_id' => $requestId,
                    'employee_id'       => $empId,
                    'kode_golongan'     => $emp['pangkat_golongan'] ?? null,
                ]);
                $memberId = $this->travelMemberModel->insertID();

                $tingkatBiaya = $this->getTingkatBiayaFromGolongan($emp['pangkat_golongan'] ?? '');

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
        if (!$this->isStaff()) {
            $employee = $this->getCurrentEmployee();
            $isMember = $employee
                ? $this->travelMemberModel->where('travel_request_id', $id)->where('employee_id', $employee['id'])->first()
                : null;
            if (!$isMember) {
                return redirect()->to('/travel')->with('error', 'Anda tidak memiliki akses ke data ini.');
            }
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);

        // Get completeness items
        $completenessModel = model('TravelCompletenessModel');
        $completeness = $completenessModel->getByRequestId($id);

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
            'tgl_mulai'                   => $this->request->getPost('tgl_mulai'),
            'tgl_selesai'                 => $this->request->getPost('tgl_selesai'),
        ];

        // Auto-calculate duration_days from tgl_mulai & tgl_selesai
        if (!empty($data['tgl_mulai']) && !empty($data['tgl_selesai'])) {
            $start = new \DateTime($data['tgl_mulai']);
            $end   = new \DateTime($data['tgl_selesai']);
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
        $calculator = new TravelExpenseCalculator();
        $totalBudgetAll = 0;
        $errors = [];

        if (!empty($memberIds) && is_array($memberIds)) {
            foreach ($memberIds as $empId) {
                $emp = $this->employeeModel->find($empId);
                if (!$emp) continue;

                $this->travelMemberModel->insert([
                    'travel_request_id' => $id,
                    'employee_id'       => $empId,
                    'kode_golongan'     => $emp['pangkat_golongan'] ?? null,
                ]);
                $memberId = $this->travelMemberModel->insertID();

                $tingkatBiaya = $this->getTingkatBiayaFromGolongan($emp['pangkat_golongan'] ?? '');

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
     * Download SPPD (.docx)
     * Signatories (PPK, KPA) can be passed via ?ppk_id & ?kpa_id query params.
     */
    public function downloadSppd(int $id): ResponseInterface
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

        $ppk = $this->resolveSignatory($this->request->getGet('ppk_id'));
        $kpa = $this->resolveSignatory($this->request->getGet('kpa_id'));

        (new \App\Libraries\Templates\SppdTemplate())->generate($travelRequest, $members, $ppk, $kpa);
        exit; // generate() streams output
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
