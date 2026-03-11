<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SignatoriesModel;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\ResponseInterface;

class SignatoriesController extends BaseController
{
    protected $signatoriesModel;
    protected $employeeModel;

    public function __construct()
    {
        $this->signatoriesModel = new SignatoriesModel();
        $this->employeeModel = new EmployeeModel();
    }

    public function index()
    {
        $signatories = $this->signatoriesModel->getAllWithEmployee();

        return view('admin/signatories/index', [
            'title'       => 'Manage Penandatangan',
            'signatories' => $signatories,
        ]);
    }

    public function create(): string|ResponseInterface
    {
        $employees = $this->employeeModel
            ->where('status', 'aktif')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('admin/signatories/create', [
            'title'     => 'Tambah Penandatangan',
            'employees' => $employees,
        ]);
    }

    public function store(): ResponseInterface
    {
        $data = [
            'jabatan'     => $this->request->getPost('jabatan'),
            'employee_id' => $this->request->getPost('employee_id'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (!$this->signatoriesModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->signatoriesModel->errors());
        }

        return redirect()->to('/admin/signatories')->with('success', 'Data Penandatangan berhasil ditambahkan');
    }

    public function edit(int $id): string|ResponseInterface
    {
        $signatory = $this->signatoriesModel->find($id);

        if (!$signatory) {
            return redirect()->to('/admin/signatories')->with('error', 'Data tidak ditemukan');
        }

        $employees = $this->employeeModel
            ->where('status', 'aktif')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('admin/signatories/edit', [
            'title'     => 'Ubah Penandatangan',
            'signatory' => $signatory,
            'employees' => $employees,
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $data = [
            'jabatan'     => $this->request->getPost('jabatan'),
            'employee_id' => $this->request->getPost('employee_id'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (!$this->signatoriesModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->signatoriesModel->errors());
        }

        return redirect()->to('/admin/signatories')->with('success', 'Data Penandatangan berhasil diubah');
    }

    public function destroy(int $id): ResponseInterface
    {
        if ($this->signatoriesModel->delete($id)) {
            return redirect()->to('/admin/signatories')->with('success', 'Penandatangan berhasil dihapus');
        }

        return redirect()->back()->with('error', 'Gagal menghapus penandatangan');
    }
}
