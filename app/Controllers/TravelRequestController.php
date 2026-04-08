<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TravelRequestController extends BaseController
{
    protected $travelRequestModel;
    protected $travelMemberModel;
    protected $travelExpenseModel;
    protected $employeeModel;
    protected $signatoryModel;

    public function __construct()
    {
        $this->travelRequestModel = model('TravelRequestModel');
        $this->travelMemberModel  = model('TravelMemberModel');
        $this->travelExpenseModel = model('TravelExpenseModel');
        $this->employeeModel      = model('EmployeeModel');
        $this->signatoryModel     = model('SignatoriesModel');
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
        $statusFilter = $this->request->getGet('status');
        $isVerificator = auth()->user()->inGroup('verificator');
        $isStaff = $this->isStaff();

        // 1. Fetch ALL relevant requests first (for correct stats)
        if ($isStaff || $isVerificator) {
            $allRequests = $this->travelRequestModel->getAllRequests();
        } else {
            $employee = $this->getCurrentEmployee();
            if (!$employee) {
                return view('travel/index', [
                    'title'          => 'Pengajuan Perdin',
                    'travelRequests' => [],
                    'isStaff'        => false,
                    'stats'          => ['total' => 0, 'draft' => 0, 'pending' => 0, 'completed' => 0],
                    'currentStatus'  => 'all'
                ]);
            }
            $memberRows = $this->travelMemberModel->where('employee_id', $employee['id'])->findAll();
            $requestIds = array_unique(array_column($memberRows, 'travel_request_id'));
            $allRequests = !empty($requestIds) ? $this->travelRequestModel->whereIn('id', $requestIds)->orderBy('created_at', 'DESC')->findAll() : [];
        }

        // 2. Attach documentation stats to ALL (to count "pending" correctly)
        $this->attachDocumentationStats($allRequests);

        // 3. Calculate summary stats from ALL
        $stats = [
            'total'     => count($allRequests),
            'draft'     => count(array_filter($allRequests, fn($r) => $r->status === 'draft')),
            'pending'   => count(array_filter($allRequests, fn($r) => $r->status === 'active' && ($r->uploaded_docs ?? 0) > 0)),
            'completed' => count(array_filter($allRequests, fn($r) => $r->status === 'completed')),
        ];

        // 4. Filter the list for display if a specific status is requested
        $travelRequests = $allRequests;
        if (!empty($statusFilter) && in_array($statusFilter, ['draft', 'active', 'completed', 'cancelled'])) {
            $travelRequests = array_filter($allRequests, fn($r) => $r->status === $statusFilter);
        }

        return view('travel/index', [
            'title'          => 'Pengajuan Perdin',
            'travelRequests' => $travelRequests,
            'isStaff'        => $isStaff,
            'stats'          => $stats,
            'currentStatus'  => $statusFilter ?? 'all',
        ]);
    }

    /**
     * List active travel requests
     */
    public function active(): string|ResponseInterface
    {
        $isVerificator = auth()->user()->inGroup('verificator');
        $isStaff = $this->isStaff();

        // reuse index logic but force 'active' status for display
        // However, we want to maintain the "Verifikasi Perdin" title for verificators
        $title = ($isVerificator && !$isStaff) ? 'Verifikasi Perdin' : 'Perjalanan Dinas Aktif';

        // Fetch ALL to get global stats
        if ($isStaff || $isVerificator) {
            $allRequests = $this->travelRequestModel->getAllRequests();
        } else {
            $employee = $this->getCurrentEmployee();
            if (!$employee) {
                return view('travel/index', [
                    'title'          => $title,
                    'travelRequests' => [],
                    'isStaff'        => false,
                    'stats'          => ['total' => 0, 'draft' => 0, 'pending' => 0, 'completed' => 0],
                    'currentStatus'  => 'active'
                ]);
            }
            $memberRows = $this->travelMemberModel->where('employee_id', $employee['id'])->findAll();
            $requestIds = array_unique(array_column($memberRows, 'travel_request_id'));
            $allRequests = !empty($requestIds) ? $this->travelRequestModel->whereIn('id', $requestIds)->orderBy('created_at', 'DESC')->findAll() : [];
        }

        $this->attachDocumentationStats($allRequests);

        $stats = [
            'total'     => count($allRequests),
            'draft'     => count(array_filter($allRequests, fn($r) => $r->status === 'draft')),
            'pending'   => count(array_filter($allRequests, fn($r) => $r->status === 'active' && ($r->uploaded_docs ?? 0) > 0)),
            'completed' => count(array_filter($allRequests, fn($r) => $r->status === 'completed')),
        ];

        // Filter for display
        $travelRequests = array_filter($allRequests, fn($r) => $r->status === 'active');

        return view('travel/index', [
            'title'          => $title,
            'travelRequests' => $travelRequests,
            'isStaff'        => $isStaff,
            'currentStatus'  => 'active',
            'stats'          => $stats,
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
            'perihal'                     => $this->request->getPost('perihal'),
            'destination_province'        => $this->formatRegionalName($this->request->getPost('destination_province')),
            'destination_city'            => $this->formatRegionalName($this->request->getPost('destination_city')),
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

        // Process members: create travel_members first
        $memberIds = $this->request->getPost('members');
        $memberGolongan = $this->request->getPost('member_golongan') ?? [];
        $totalBudgetAll = 0;

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

                // Initialize empty expenses. Will be filled during enrichment (CompletenessController).
                $this->travelExpenseModel->insert([
                    'travel_member_id'  => $memberId,
                    'uang_harian'       => 0,
                    'uang_representasi' => 0,
                    'penginapan'        => 0,
                    'tiket'             => 0,
                    'transport_darat'   => 0,
                    'transport_lokal'   => 0,
                    'total_biaya'       => 0,
                    'tariff_id'         => null,
                ]);
            }
        }

        $this->travelRequestModel->update($requestId, ['total_budget' => 0]);

        $successMsg = 'Pengajuan perjalanan dinas berhasil disimpan. Silahkan lengkapi rincian biaya di halaman Lengkapi Data.';

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

        // Fetch models for files and completeness
        $completenessModel = model('TravelCompletenessModel');
        $fileModel = model('TravelCompletenessFileModel');
        $expenseItemModel = new \App\Models\TravelExpenseItemModel();

        foreach ($members as &$member) {
            // Fetch itemized expenses (Phase 8)
            $member->expense_items = $expenseItemModel->where('travel_member_id', $member->travel_member_id)->findAll();

            // Fetch documentation files for this specific member
            // Logic: files where member_id matches OR (member_id is NULL and uploaded_by matches this member's user_id)
            $member->documentation_files = $fileModel->join('travel_completeness', 'travel_completeness.id = travel_completeness_files.completeness_id')
                ->where('travel_completeness.travel_request_id', $id)
                ->groupStart()
                ->where('travel_completeness.member_id', $member->travel_member_id)
                ->orGroupStart()
                ->where('travel_completeness.member_id', null)
                ->where('travel_completeness_files.uploaded_by', $member->user_id)
                ->groupEnd()
                ->groupEnd()
                ->select('travel_completeness_files.*, travel_completeness.item_name')
                ->findAll();
        }

        // Get global/legacy completeness items (where member_id is NULL)
        $legacyCompleteness = $completenessModel->where('travel_request_id', $id)
            ->where('member_id', null)
            ->findAll();

        foreach ($legacyCompleteness as $item) {
            $item->files = $fileModel->getByCompletenessId($item->id);
        }

        // Legacy items go to global completeness display if needed
        $completeness = $legacyCompleteness;

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
            'perihal'                     => $this->request->getPost('perihal'),
            'destination_province'        => $this->formatRegionalName($this->request->getPost('destination_province')),
            'destination_city'            => $this->formatRegionalName($this->request->getPost('destination_city')),
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

        // Re-create members
        $memberIds = $this->request->getPost('members');
        $memberGolongan = $this->request->getPost('member_golongan') ?? [];

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

                // Initialize empty expenses.
                $this->travelExpenseModel->insert([
                    'travel_member_id'  => $memberId,
                    'uang_harian'       => 0,
                    'uang_representasi' => 0,
                    'penginapan'        => 0,
                    'tiket'             => 0,
                    'transport_darat'   => 0,
                    'transport_lokal'   => 0,
                    'total_biaya'       => 0,
                    'tariff_id'         => null,
                ]);
            }
        }

        $this->travelRequestModel->update($id, ['total_budget' => 0]);

        return redirect()->to('/travel/' . $id)->with('success', 'Perubahan berhasil disimpan.');
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
        if (!$this->isStaff()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
        }

        // Only allow cancelling active or draft requests
        if (!in_array($travelRequest->status, ['draft', 'active'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya pengajuan berstatus draft atau aktif yang dapat dibatalkan.']);
        }

        $this->travelRequestModel->update($id, ['status' => 'cancelled']);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Perjalanan dinas telah dibatalkan.']);
    }

    public function complete(int $id): ResponseInterface
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak. Hanya Superadmin yang dapat melakukan tindakan ini.']);
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
        }

        if ($travelRequest->status !== 'active') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya pengajuan berstatus aktif yang dapat ditandai selesai.']);
        }

        $this->travelRequestModel->update($id, ['status' => 'completed']);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Perjalanan dinas telah ditandai sebagai selesai.']);
    }

    /**
     * PolsriPay: Search employees with filter
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
     * PolsriPay: Real-time tariff check (Deprecated - No longer used)
     */
    public function checkTariff(): ResponseInterface
    {
        return $this->response->setJSON(['status' => 'success', 'message' => 'Manual input enabled']);
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

        $isStaff = $this->isStaff();
        $emp = $this->getCurrentEmployee();
        $specificMemberId = $this->request->getGet('member_id');
        $showBackPage = $specificMemberId === null; // Show back page only for full download

        if (!$isStaff) {
            $isMember = $emp
                ? $this->travelMemberModel->where('travel_request_id', $id)->where('employee_id', $emp['id'])->first()
                : null;
            if (!$isMember) {
                return redirect()->to('/travel')->with('error', 'Akses ditolak.');
            }
            // Lecturer can only see their own Individual SPD (1 page)
            $specificMemberId = is_array($isMember) ? $isMember['id'] : $isMember->id;
            $showBackPage = false; 
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);

        // Resolve PPK: prioritize ppk_id from travel_requests, then fallback to active PPK
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

        if ($this->request->getGet('format') === 'pdf') {
            (new \App\Libraries\Templates\SppdPdfTemplate())->generate($travelRequest, $members, $ppk, $specificMemberId, $showBackPage);
        } else {
            (new \App\Libraries\Templates\SppdTemplate())->generate($travelRequest, $members, $ppk, $specificMemberId, $showBackPage);
        }
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
            $specificMemberId = is_array($isMember) ? $isMember['id'] : $isMember->id;
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);
        $customDate = $this->request->getGet('stmt_date');

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

        if ($this->request->getGet('format') === 'pdf') {
            (new \App\Libraries\Templates\SuratPernyataanPdfTemplate())->generate($travelRequest, $members, $ppk, $specificMemberId, $customDate);
        } else {
            (new \App\Libraries\Templates\SuratPernyataanTemplate())->generate($travelRequest, $members, $ppk, $specificMemberId, $customDate);
        }
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

        // Resolve Bendahara
        $bendaharaId = $travelRequest->bendahara_id;
        $bendahara = $this->resolveSignatory((string) $bendaharaId);

        if (!$bendahara) {
            $bendaharaSig = $this->signatoryModel
                ->like('jabatan', 'Bendahara Pengeluaran')
                ->where('is_active', 1)
                ->first();
            if ($bendaharaSig) {
                $bendahara = $this->resolveSignatory((string) $bendaharaSig->id);
            }
        }

        (new \App\Libraries\Templates\DaftarKontrolTemplate())->generate($travelRequest, $members, $bendahara);
        exit;
    }

    /**
     * Generate and download Daftar Nominatif (Excel).
     */
    public function downloadNominativeList(int $id): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel')->with('error', 'Akses ditolak.');
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);

        // Resolve Bendahara
        $bendaharaId = $travelRequest->bendahara_id;
        $bendahara = $this->resolveSignatory((string) $bendaharaId);

        if (!$bendahara) {
            $bendaharaSig = $this->signatoryModel
                ->like('jabatan', 'Bendahara Pengeluaran')
                ->notLike('jabatan', 'Pembantu')
                ->where('is_active', 1)
                ->first();
            if ($bendaharaSig) {
                $bendahara = $this->resolveSignatory((string) $bendaharaSig->id);
            }
        }

        (new \App\Libraries\Templates\DaftarNominatifTemplate())->generate($travelRequest, $members, $bendahara);
        exit;
    }

    /**
     * Generate and download Bundle Excel (all documents in one file).
     */
    public function downloadBundleExcel(int $id): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel')->with('error', 'Akses ditolak.');
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/travel')->with('error', 'Data tidak ditemukan.');
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

        // Resolve Bendahara Pengeluaran
        $bendaharaId = $travelRequest->bendahara_id;
        $bendahara = $this->resolveSignatory((string) $bendaharaId);
        if (!$bendahara) {
            $bendaharaSig = $this->signatoryModel
                ->like('jabatan', 'Bendahara Pengeluaran')
                ->notLike('jabatan', 'Pembantu')
                ->where('is_active', 1)
                ->first();
            if ($bendaharaSig) {
                $bendahara = $this->resolveSignatory((string) $bendaharaSig->id);
            }
        }

        (new \App\Libraries\Templates\BundleExcelTemplate())->generate($travelRequest, $members, $ppk, $bendahara);
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

    /**
     * Helper to attach documentation stats to each member in travel requests.
     */
    private function attachDocumentationStats(&$travelRequests)
    {
        $completenessModel = new \App\Models\TravelCompletenessModel();
        $fileModel = new \App\Models\TravelCompletenessFileModel();
        $currentUserId = auth()->id();

        foreach ($travelRequests as &$req) {
            $req->members = $this->travelMemberModel->getByRequestWithEmployee($req->id);

            // Only fetch stats if active/completed to optimize performance
            if (in_array($req->status, ['active', 'completed'])) {
                $items = $completenessModel->where('travel_request_id', $req->id)->findAll();
                $allFiles = $fileModel->whereIn('completeness_id', array_column($items, 'id') ?: [0])->findAll();

                $req->total_docs = count($items);
                $req->uploaded_docs = count(array_filter($items, fn($i) => $i->status === 'uploaded'));
                $req->verified_docs = count(array_filter($items, fn($i) => $i->status === 'verified'));

                // Calculate PER MEMBER stats
                foreach ($req->members as &$member) {
                    $mUserId = $member->user_id ?? -1;
                    $memberItems = array_filter($items, function ($i) use ($member, $mUserId, $allFiles) {
                        // 1. Direct match by member_id
                        if ($i->member_id == $member->id) return true;

                        // 2. Legacy check: member_id is null AND this user has files for this item
                        if ($i->member_id == null) {
                            foreach ($allFiles as $f) {
                                if ($f->completeness_id == $i->id && $f->uploaded_by == $mUserId) {
                                    return true;
                                }
                            }
                        }
                        return false;
                    });

                    $member->total_docs = count($memberItems);
                    // Use uploaded_docs for logic in view (including uploaded & verified for progress)
                    $member->uploaded_docs = count(array_filter($memberItems, fn($i) => $i->status === 'uploaded' || $i->status === 'verified'));
                    $member->uploaded_count = count(array_filter($memberItems, fn($i) => $i->status === 'uploaded'));
                    $member->verified_docs = count(array_filter($memberItems, fn($i) => $i->status === 'verified'));

                    // If this is the current user, attach personal stats to request
                    if ($mUserId == $currentUserId) {
                        $req->personal_stats = [
                            'total' => $member->total_docs,
                            'uploaded' => $member->uploaded_count,
                            'verified' => $member->verified_docs
                        ];
                    }
                }
            } else {
                $req->total_docs = 0;
                $req->uploaded_docs = 0;
                $req->verified_docs = 0;
                foreach ($req->members as &$member) {
                    $member->total_docs = 0;
                    $member->uploaded_docs = 0;
                    $member->verified_docs = 0;
                }
            }
        }
    }
    private function formatRegionalName(?string $name): ?string
    {
        if (!$name) return null;
        
        $acronyms = ['DKI', 'DI', 'NTB', 'NTT', 'NAD', 'DIY'];
        $words = explode(' ', strtolower($name));
        
        $formatted = array_map(function($word) use ($acronyms) {
            $upper = strtoupper($word);
            if (in_array($upper, $acronyms)) {
                return $upper;
            }
            return ucfirst($word);
        }, $words);

        return implode(' ', $formatted);
    }
}
