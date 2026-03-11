<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\User;

class UserController extends BaseController
{
    protected EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->employeeModel = model(EmployeeModel::class);
    }

    public function index(): string
    {
        $data['users'] = auth()->getProvider()->findAll();
        $data['title'] = 'Manajemen User';

        return view('admin/users/index', $data);
    }

    public function create(int $empId): string|ResponseInterface
    {
        $employee = $this->employeeModel->find($empId);

        if (!$employee) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        if ($employee['user_id']) {
            return redirect()->back()->with('error', 'Pegawai sudah memiliki akun.');
        }

        $data['title'] = 'Buat Akun User';
        $data['employee'] = $employee;
        $data['availableGroups'] = config('AuthGroups')->groups;
        return view('admin/users/create', $data);
    }

    public function store(): ResponseInterface
    {
        $empId = $this->request->getPost('employee_id', FILTER_VALIDATE_INT);
        $group = $this->request->getPost('group');
        $email = $this->request->getPost('email', FILTER_SANITIZE_EMAIL);

        $employee = $this->employeeModel->find($empId);

        if ($employee === null) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        $nip     = (string) $employee['nip'];
        $name    = (string) $employee['name'];
        $jurusan = (string) $employee['nama_jurusan'];

        $words = explode(' ', $jurusan);
        $jurPart = '';
        foreach ($words as $w) {
            $jurPart .= strtolower(substr($w, 0, 3));
        }

        $namePart = strtolower(preg_replace("/[^A-Za-z0-9]/", '', explode(' ', $name)[0]));

        $nipPart = substr($nip, -4);

        // Hasil Akhir
        $username = "{$namePart}_{$jurPart}{$nipPart}";

        $rawPassword = bin2hex(random_bytes(5));

        $userProvider = auth()->getProvider();
        $newUser = new User([
            'username' => $username,
            'password' => $rawPassword,
            'email'    => $email,
            'active'   => true,
        ]);
        $userProvider->save($newUser);
        $newUser = $userProvider->findById($userProvider->getInsertID());
        $newUser->addGroup($group);

        $this->employeeModel->update($empId, ['user_id' => $newUser->id]);

        session()->setFlashdata('new_credential', [
            'username' => $username,
            'password' => $rawPassword,
        ]);

        return redirect()->to('/admin/users/credential');
    }

    public function showCredential(): string|ResponseInterface
    {
        $credential = session()->getFlashdata('new_credential');

        if (!$credential) {
            return redirect()->to('/admin/users')->with('error', 'Credential sudah tidak tersedia.');
        }

        return view('admin/users/credential', ['credential' => $credential]);
    }

    public function edit(int $userId): string|ResponseInterface
    {
        $user = auth()->getProvider()->findById($userId);

        $data['title'] = 'Edit Role User';
        $data['user'] = $user;
        $data['availableGroups'] = config('AuthGroups')->groups;
        return view('admin/users/edit', $data);
    }

    public function update(int $userId): ResponseInterface
    {
        $newGroup = $this->request->getPost('group');
        $newEmail = $this->request->getPost('email', FILTER_SANITIZE_EMAIL);

        $user = auth()->getProvider()->findById($userId);

        if ($user === null) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        // Update email (Shield menyimpannya di auth_identities)
        if ($newEmail && $newEmail !== $user->email) {
            $user->fill(['email' => $newEmail]);
            auth()->getProvider()->save($user);
        }

        // Update role
        $user->syncGroups($newGroup);

        return redirect()->to('/admin/users')->with('success', 'Data user berhasil diubah.');
    }

    public function resetPassword(int $userId): ResponseInterface
    {
        $user = auth()->getProvider()->findById($userId);

        if ($user === null) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $rawPassword = bin2hex(random_bytes(5));
        $user->fill(['password' => $rawPassword]);
        auth()->getProvider()->save($user);

        session()->setFlashdata('new_credential', [
            'username' => $user->username,
            'password' => $rawPassword,
        ]);

        return redirect()->to('/admin/users/credential');
    }

    public function toggleActive(int $userId): ResponseInterface
    {
        $user = auth()->getProvider()->findById($userId);

        if ($user === null) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $user->isBanned() ? $user->unBan() : $user->ban('User dinonaktifkan');
        auth()->getProvider()->save($user);

        return redirect()->back()->with('success', 'Status user diperbarui.');
    }

    public function destroy(int $userId): ResponseInterface
    {
        $user = auth()->getProvider()->findById($userId);

        if ($user === null) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        auth()->getProvider()->delete($userId, true);
        $this->employeeModel->where('user_id', $userId)->set(['user_id' => null])->update();

        return redirect()->to('/admin/users')->with('success', 'User dihapus.');
    }
}
