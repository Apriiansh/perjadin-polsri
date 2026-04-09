<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\StudentModel;
use App\Models\StudentTravelMemberModel;
use App\Models\TravelRequestModel;
use CodeIgniter\Shield\Entities\User;

class StudentController extends BaseController
{
    protected $studentModel;
    protected $memberModel;
    protected $travelModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->memberModel  = new StudentTravelMemberModel();
        $this->travelModel  = new TravelRequestModel();
    }

    public function index(): string
    {
        // Subquery to get the latest travel request ID for each student
        $db = \Config\Database::connect();
        $latestTravelSubquery = $db->table('travel_student_members')
            ->select('student_id, MAX(travel_request_id) as latest_request_id')
            ->groupBy('student_id')
            ->getCompiledSelect();

        $students = $this->studentModel
            ->select('students.*, tr.id as last_travel_id, tr.departure_date, tr.destination_city, tr.destination_province')
            ->join("($latestTravelSubquery) lt", 'lt.student_id = students.id', 'left')
            ->join('travel_requests tr', 'tr.id = lt.latest_request_id', 'left')
            ->findAll();

        return view('admin/students/index', [
            'title'    => 'Manage Mahasiswa',
            'students' => $students,
        ]);
    }

    public function show(int $id): string|ResponseInterface
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            return redirect()->to('/admin/students')->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $travels = $this->memberModel
            ->select('travel_student_members.*, travel_requests.no_surat_tugas, travel_requests.perihal, travel_requests.status, travel_requests.tgl_surat_tugas')
            ->join('travel_requests', 'travel_requests.id = travel_student_members.travel_request_id')
            ->where('student_id', $id)
            ->orderBy('travel_requests.created_at', 'DESC')
            ->findAll();

        return view('admin/students/show', [
            'title'   => 'Detail Mahasiswa',
            'student' => $student,
            'travels' => $travels,
        ]);
    }

    public function edit(int $id): string|ResponseInterface
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            return redirect()->to('/admin/students')->with('error', 'Mahasiswa tidak ditemukan.');
        }

        return view('admin/students/edit', [
            'title'   => 'Edit Mahasiswa',
            'student' => $student,
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            return redirect()->to('/admin/students')->with('error', 'Mahasiswa tidak ditemukan.');
        }

        $rules = [
            'nim'     => "required|string|max_length[20]|is_unique[students.nim,id,{$id}]",
            'name'    => 'required|string|max_length[200]',
            'prodi'   => 'required|string|max_length[100]',
            'jurusan' => 'required|string|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $oldNim = $student->nim;
        $newNim = $this->request->getPost('nim');

        $data = [
            'nim'     => $newNim,
            'name'    => $this->request->getPost('name'),
            'prodi'   => $this->request->getPost('prodi'),
            'jurusan' => $this->request->getPost('jurusan'),
        ];

        if ($this->studentModel->update($id, $data)) {
            // Sync with Shield User if NIM changed and user exists
            if ($oldNim !== $newNim && !empty($student->user_id)) {
                $users = auth()->getProvider();
                $user = $users->findById($student->user_id);
                if ($user) {
                    $user->fill([
                        'email' => strtolower($newNim) . '@polsri.ac.id',
                    ]);
                    $users->save($user);
                }
            }

            return redirect()->to('/admin/students/' . $id)->with('success', 'Data mahasiswa berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data.');
    }

    public function resetPassword(int $id): ResponseInterface
    {
        $student = $this->studentModel->find($id);
        if (!$student || empty($student->user_id)) {
            return redirect()->back()->with('error', 'Akun tidak ditemukan atau mahasiswa belum memiliki akun.');
        }

        $users = auth()->getProvider();
        $user = $users->findById($student->user_id);
        
        if (!$user) {
            return redirect()->back()->with('error', 'User record not found.');
        }

        // Generate simple reset password
        $newPassword = 'Reset' . bin2hex(random_bytes(2)) . '!';
        
        $user->fill([
            'password' => $newPassword,
        ]);
        $users->save($user);

        $newCredential = [
            'username' => $user->username,
            'password' => $newPassword,
            'name'     => $student->name
        ];

        return redirect()->to('/travel/student/credential')->with('newCredential', $newCredential);
    }

    public function destroy(int $id): ResponseInterface
    {
        $student = $this->studentModel->find($id);
        if (!$student) {
            return redirect()->to('/admin/students')->with('error', 'Mahasiswa tidak ditemukan.');
        }

        // Check if student has travel history
        $hasTravel = $this->memberModel->where('student_id', $id)->countAllResults() > 0;
        if ($hasTravel) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus mahasiswa yang memiliki riwayat perjalanan dinas.');
        }

        if ($this->studentModel->delete($id)) {
            return redirect()->to('/admin/students')->with('success', 'Data mahasiswa berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }
}
