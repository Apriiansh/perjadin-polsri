<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TravelRequestModel;
use App\Models\StudentModel;
use App\Models\StudentTravelMemberModel;
use App\Models\StudentTravelExpenseItemModel;
use App\Models\StudentTravelCompletenessModel;
use App\Models\SignatoriesModel;
use CodeIgniter\Shield\Entities\User;

class StudentTravelController extends BaseController
{
    protected $travelRequestModel;
    protected $studentModel;
    protected $memberModel;
    protected $expenseModel;
    protected $signatoryModel;

    public function __construct()
    {
        $this->travelRequestModel = new TravelRequestModel();
        $this->studentModel       = new StudentModel();
        $this->memberModel       = new StudentTravelMemberModel();
        $this->expenseModel      = new StudentTravelExpenseItemModel();
        $this->signatoryModel     = new SignatoriesModel();
    }

    private function isStaff(): bool
    {
        $groups = auth()->user()?->getGroups() ?? [];
        return in_array('admin', $groups) || in_array('superadmin', $groups);
    }

    public function index(): string
    {
        $statusFilter = $this->request->getGet('status');
        $isVerificator = auth()->user()->inGroup('verificator');
        $representativeRequestIds = [];
        $query = $this->travelRequestModel->where('category', 'mahasiswa');

        // Scoping for students: only see travels where you are a member
        if (!$this->isStaff() && !$isVerificator) {
            $student = $this->studentModel->where('user_id', auth()->id())->first();
            if ($student) {
                $memberRows = $this->memberModel->where('student_id', $student->id)->findAll();
                $requestIds = [];
                $representativeRequestIds = [];
                foreach ($memberRows as $row) {
                    $requestId = (int) ($row->travel_request_id ?? 0);
                    if ($requestId <= 0) {
                        continue;
                    }

                    $requestIds[] = $requestId;
                    if ((int) ($row->is_representative ?? 0) === 1) {
                        $representativeRequestIds[] = $requestId;
                    }
                }
                $requestIds = array_values(array_unique($requestIds));
                $representativeRequestIds = array_values(array_unique($representativeRequestIds));
                
                if (!empty($requestIds)) {
                    $query->whereIn('id', $requestIds);
                } else {
                    $query->where('id', 0); // No results
                }
            } else {
                $query->where('id', 0); // Student record missing
            }
        }

        $allRequests = $query->orderBy('created_at', 'DESC')->findAll();

        $stats = [
            'total'     => count($allRequests),
            'draft'     => count(array_filter($allRequests, fn($r) => $r->status === 'draft')),
            'active'    => count(array_filter($allRequests, fn($r) => $r->status === 'active')),
            'completed' => count(array_filter($allRequests, fn($r) => $r->status === 'completed')),
        ];

        $requests = $allRequests;
        if (!empty($statusFilter) && in_array($statusFilter, ['draft', 'active', 'completed', 'cancelled'])) {
            $requests = array_filter($allRequests, fn($r) => $r->status === $statusFilter);
        }

        $requestIds = array_values(array_map(static fn($r) => (int) $r->id, $requests));
        $membersByRequest = [];
        if (!empty($requestIds)) {
            $memberRows = $this->memberModel
                ->select('travel_student_members.*, students.nim, students.name, students.prodi, students.jurusan')
                ->join('students', 'students.id = travel_student_members.student_id')
                ->whereIn('travel_request_id', $requestIds)
                ->orderBy('is_representative', 'DESC')
                ->orderBy('id', 'ASC')
                ->findAll();

            foreach ($memberRows as $memberRow) {
                $membersByRequest[(int) $memberRow->travel_request_id][] = $memberRow;
            }
        }

        return view('travel/student/index', [
            'title'         => 'Perjadin Mahasiswa',
            'requests'      => $requests,
            'isStaff'       => $this->isStaff(),
            'isVerificator' => $isVerificator,
            'stats'         => $stats,
            'currentStatus' => $statusFilter ?? 'all',
            'membersByRequest' => $membersByRequest,
            'representativeRequestIds' => $representativeRequestIds,
        ]);
    }

    public function create(): string|ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel/student')->with('error', 'Akses ditolak.');
        }

        return view('travel/student/create', [
            'title' => 'Buat Perjadin Mahasiswa',
        ]);
    }

    public function store(): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel/student')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Save Header
            $requestData = [
                'no_surat_tugas'            => $this->request->getPost('no_surat_tugas'),
                'tgl_surat_tugas'           => $this->request->getPost('tgl_surat_tugas'),
                'nomor_surat_rujukan'       => $this->request->getPost('nomor_surat_rujukan'),
                'tgl_surat_rujukan'         => $this->request->getPost('tgl_surat_rujukan'),
                'instansi_pengirim_rujukan' => $this->request->getPost('instansi_pengirim_rujukan'),
                'perihal'                   => $this->request->getPost('perihal'),
                'destination_province'      => $this->request->getPost('destination_province'),
                'destination_city'          => $this->request->getPost('destination_city'),
                'departure_place'           => $this->request->getPost('departure_place') ?: 'Palembang',
                'lokasi'                    => $this->request->getPost('lokasi'),
                'departure_date'            => $this->request->getPost('departure_date'),
                'return_date'               => $this->request->getPost('return_date'),
                'budget_burden_by'          => $this->request->getPost('budget_burden_by'),
                'tahun_anggaran'            => $this->request->getPost('tahun_anggaran'),
                'category'                  => 'mahasiswa',
                'status'                    => 'draft',
                'created_by'                => auth()->id(),
            ];

            // Handle Lampiran upload
            $lampiran = $this->request->getFile('lampiran');
            if ($lampiran && $lampiran->isValid() && !$lampiran->hasMoved()) {
                $newName = $lampiran->getRandomName();
                $lampiran->move(WRITEPATH . 'uploads/travel', $newName);
                $requestData['lampiran_path']          = 'travel/' . $newName;
                $requestData['lampiran_original_name']  = $lampiran->getClientName();
            }

            // Duration
            $start = new \DateTime($requestData['departure_date']);
            $end   = new \DateTime($requestData['return_date']);
            $requestData['duration_days'] = (int) $start->diff($end)->days + 1;

            if (!$this->travelRequestModel->insert($requestData)) {
                throw new \Exception('Gagal menyimpan data pengajuan.');
            }
            $requestId = $this->travelRequestModel->getInsertID();

            // 2. Process Students
            $students = $this->request->getPost('students'); // Array of objects
            if (empty($students)) {
                throw new \Exception('Minimal harus ada 1 mahasiswa.');
            }

            $newCredential = null;

            foreach ($students as $index => $s) {
                // Find or create student
                $studentObj = $this->studentModel->where('nim', $s['nim'])->first();
                if (!$studentObj) {
                    $studentId = $this->studentModel->insert([
                        'nim'     => $s['nim'],
                        'name'    => $s['name'],
                        'prodi'   => $s['prodi'],
                        'jurusan' => $s['jurusan'],
                    ]);
                } else {
                    $studentId = $studentObj->id;
                    $this->studentModel->update($studentId, [
                        'name'    => $s['name'],
                        'prodi'   => $s['prodi'],
                        'jurusan' => $s['jurusan'],
                    ]);
                }

                $isLeader = ($index == 0); // First student is leader

                // Create User Account if leader and doesn't have one
                if ($isLeader) {
                    /** @var object $currentStudent */
                    $currentStudent = $this->studentModel->find($studentId);
                    if ($currentStudent && empty($currentStudent->user_id)) {
                        $users = auth()->getProvider();
                        
                        // Generate random secure password
                        $rawPassword = bin2hex(random_bytes(4)) . '!'; 
                        
                        // Generate username: first name + last 4 NIM digits
                        $firstName = strtolower(explode(' ', trim($s['name']))[0]);
                        $nimSuffix = substr($s['nim'], -4);
                        $username  = $firstName . $nimSuffix;

                        $user = new User([
                            'username' => $username,
                            'email'    => strtolower($s['nim']) . '@polsri.ac.id',
                            'password' => $rawPassword,
                        ]);
                        
                        $users->save($user);
                        $user = $users->findById($users->getInsertID());
                        $user->addGroup('student');
                        
                        $this->studentModel->update($studentId, ['user_id' => $user->id]);
                        
                        // Save for credential page
                        $newCredential = [
                            'username' => $user->username,
                            'password' => $rawPassword,
                            'name'     => $s['name']
                        ];
                    }
                }

                // Save Member
                $this->memberModel->insert([
                    'travel_request_id' => $requestId,
                    'student_id'        => $studentId,
                    'jabatan'           => $isLeader ? 'Ketua' : 'Anggota',
                    'is_representative' => $isLeader ? 1 : 0,
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal.');
            }

            // If account was created, show credentials
            if ($newCredential) {
                return redirect()->to('/travel/student/credential')->with('newCredential', $newCredential);
            }

            return redirect()->to('/travel/student/' . $requestId)->with('success', 'Pengajuan berhasil disimpan.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display credentials for newly created student account
     */
    public function credential(): string|ResponseInterface
    {
        $credential = session('newCredential');
        if (!$credential) {
            return redirect()->to('/travel/student')->with('error', 'Halaman kadaluarsa.');
        }

        return view('travel/student/credential', [
            'title'      => 'Akun Mahasiswa Berhasil Dibuat',
            'credential' => $credential,
        ]);
    }

    public function show(int $id): string|ResponseInterface
    {
        /** @var object|null $request */
        $request = $this->travelRequestModel->getDetail($id);
        if (!$request || $request->category !== 'mahasiswa') {
            return redirect()->to('/travel/student')->with('error', 'Data tidak ditemukan.');
        }

        $isVerificator = auth()->user()->inGroup('verificator');
        $isStaff = $this->isStaff();
        $student = null;
        $memberForCurrentUser = null;

        // Access control: student can only open request where they are a member.
        if (!$isStaff && !$isVerificator) {
            $student = $this->studentModel->where('user_id', auth()->id())->first();
            if (!$student) {
                return redirect()->to('/travel/student')->with('error', 'Akses ditolak.');
            }

            $memberForCurrentUser = $this->memberModel->where([
                'travel_request_id' => $id,
                'student_id'        => $student->id,
            ])->first();
            if (!$memberForCurrentUser) {
                return redirect()->to('/travel/student')->with('error', 'Akses ditolak. Anda tidak terdaftar pada tim perjadin ini.');
            }
        }

        $members = $this->memberModel->getByRequestId($id);
        foreach ($members as &$m) {
            $m->expenses = $this->expenseModel->getByMemberId($m->id);
            $m->total_amount = array_sum(array_column($m->expenses, 'amount'));
        }

        // Fetch Signatories
        if ($request->ppk_id) {
            /** @var object|null $ppk */
            $ppk = $this->signatoryModel->select('employees.name, employees.nip')
                ->join('employees', 'employees.id = signatories.employee_id')
                ->find($request->ppk_id);
            if ($ppk) {
                $request->ppk_name = $ppk->name;
                $request->ppk_nip = $ppk->nip;
            }
        }
        if ($request->bendahara_id) {
            /** @var object|null $bendahara */
            $bendahara = $this->signatoryModel->select('employees.name, employees.nip')
                ->join('employees', 'employees.id = signatories.employee_id')
                ->find($request->bendahara_id);
            if ($bendahara) {
                $request->bendahara_name = $bendahara->name;
                $request->bendahara_nip = $bendahara->nip;
            }
        }

        $isLeader = false;
        if (auth()->user()->inGroup('student')) {
            if (!$student) {
                $student = $this->studentModel->where('user_id', auth()->id())->first();
            }
            if ($student) {
                if ($memberForCurrentUser) {
                    $isLeader = ((int) ($memberForCurrentUser->is_representative ?? 0) === 1);
                } else {
                    $leaderMember = $this->memberModel->where([
                        'travel_request_id' => $id,
                        'student_id'        => $student->id,
                        'is_representative' => 1
                    ])->first();
                    $isLeader = !empty($leaderMember);
                }
            }
        }

        // Check if documentation exists (at least one item uploaded or verified)
        $completenessModel = new StudentTravelCompletenessModel();
        $uploadedDocsCount = $completenessModel->where('travel_request_id', $id)
            ->where('status', 'uploaded')
            ->countAllResults();
        $verifiedDocsCount = $completenessModel->where('travel_request_id', $id)
            ->where('status', 'verified')
            ->countAllResults();
        $rejectedDocsCount = $completenessModel->where('travel_request_id', $id)
            ->where('status', 'rejected')
            ->countAllResults();

        $hasDocumentation = ($uploadedDocsCount + $verifiedDocsCount) > 0;
        $hasPendingValidationDocs = $uploadedDocsCount > 0;
        $hasRejectedDocs = $rejectedDocsCount > 0;
        $hasVerifiedDocs = $verifiedDocsCount > 0;

        return view('travel/student/show', [
            'title'            => 'Detail Perjadin Mahasiswa',
            'request'          => $request,
            'members'          => $members,
            'isStaff'          => $isStaff,
            'isLeader'         => $isLeader,
            'student'          => $student,
            'hasDocumentation' => $hasDocumentation,
            'hasPendingValidationDocs' => $hasPendingValidationDocs,
            'hasRejectedDocs'  => $hasRejectedDocs,
            'hasVerifiedDocs'  => $hasVerifiedDocs,
        ]);
    }

    public function downloadReport(int $id): void
    {
        $request = $this->travelRequestModel->getDetail($id);
        if (!$request || $request->category !== 'mahasiswa') {
            exit('Data tidak ditemukan.');
        }

        $members = $this->memberModel->getByRequestId($id);
        foreach ($members as &$m) {
            $m->expenses = $this->expenseModel->getByMemberId($m->id);
            $m->total_amount = array_sum(array_column($m->expenses, 'amount'));
        }

        /** @var object|null $ppk */
        $ppk = $this->signatoryModel->select('employees.name, employees.nip')
            ->join('employees', 'employees.id = signatories.employee_id')
            ->find($request->ppk_id);
            
        /** @var object|null $bendahara */
        $bendahara = $this->signatoryModel->select('employees.name, employees.nip')
            ->join('employees', 'employees.id = signatories.employee_id')
            ->find($request->bendahara_id);

        $template = new \App\Libraries\Templates\StudentTravelTemplate();
        $template->generate($request, $members, $ppk, $bendahara);
    }

    /**
     * Delete travel request — staff only
     */
    public function destroy(int $id): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->to('/travel/student')->with('error', 'Anda tidak memiliki akses.');
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest || $travelRequest->category !== 'mahasiswa') {
            return redirect()->to('/travel/student')->with('error', 'Data tidak ditemukan.');
        }

        // Delete uploaded lampiran if exists
        if (!empty($travelRequest->lampiran_path)) {
            $filePath = WRITEPATH . 'uploads/' . $travelRequest->lampiran_path;
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        $this->travelRequestModel->delete($id);

        return redirect()->to('/travel/student')->with('success', 'Pengajuan perjalanan dinas mahasiswa berhasil dihapus.');
    }

    /**
     * Cancel active request — staff only
     */
    public function cancel(int $id): ResponseInterface
    {
        if (!$this->isStaff()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest || $travelRequest->category !== 'mahasiswa') {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // Only allow cancelling active or draft requests
        if (!in_array($travelRequest->status, ['draft', 'active'])) {
            return redirect()->back()->with('error', 'Hanya pengajuan berstatus draft atau aktif yang dapat dibatalkan.');
        }

        $this->travelRequestModel->update($id, ['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Perjalanan dinas mahasiswa telah dibatalkan.');
    }
}
