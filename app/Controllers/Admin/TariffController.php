<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TariffModel;
use CodeIgniter\HTTP\ResponseInterface;

class TariffController extends BaseController
{
    protected $tariffModel;

    public function __construct()
    {
        $this->tariffModel = new TariffModel();
    }

    public function index()
    {
        $tariff = $this->tariffModel
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return view('admin/tariffs/index', [
            'title' => 'Manage Tarif',
            'tariffs' => $tariff,
        ]);
    }

    public function create(): string|ResponseInterface
    {
        $data = [
            'title' => 'Tamabah Tarif'
        ];

        return view('admin/tariffs/create', $data);
    }

    public function store(): ResponseInterface
    {
        $data = [
            'province' => $this->request->getPost('province'),
            'city' => $this->request->getPost('city') ?: null,
            'tingkat_biaya' => $this->request->getPost('tingkat_biaya'),
            'uang_harian' => $this->request->getPost('uang_harian'),
            'uang_representasi' => $this->request->getPost('uang_representasi'),
            'penginapan' => $this->request->getPost('penginapan'),
            'jenis_penginapan' => $this->request->getPost('jenis_penginapan') ?: 'Standar Hotel',
            'tahun_berlaku' => $this->request->getPost('tahun_berlaku'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (!$this->tariffModel->insert($data)) {
            $errorMsg = $this->tariffModel->errors();
            return redirect()->back()->withInput()->with('errors', $errorMsg);
        }

        return redirect()->to('/admin/tariffs')->with('success', 'Berhasil menambahkan data');
    }

    public function edit(int $tariffId): string|ResponseInterface
    {
        $tariff = $this->tariffModel->find($tariffId);

        if ($tariff === null) {
            return redirect()->to('/admin/tariffs')->with('error', 'Data tidak ditemukan');
        }

        $data = [
            'title' => 'Ubah Tarif',
            'tariff' => $tariff,
        ];

        return view('admin/tariffs/edit', $data);
    }
    public function update(int $tariffId): ResponseInterface
    {
        $data = [
            'province' => $this->request->getPost('province'),
            'city' => $this->request->getPost('city') ?: null,
            'tingkat_biaya' => $this->request->getPost('tingkat_biaya'),
            'uang_harian' => $this->request->getPost('uang_harian'),
            'uang_representasi' => $this->request->getPost('uang_representasi'),
            'penginapan' => $this->request->getPost('penginapan'),
            'jenis_penginapan' => $this->request->getPost('jenis_penginapan') ?: 'Standar Hotel',
            'tahun_berlaku' => $this->request->getPost('tahun_berlaku'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (!$this->tariffModel->update($tariffId, $data)) {
            $errorMsg = $this->tariffModel->errors();
            return redirect()->back()->withInput()->with('errors', $errorMsg);
        }

        return redirect()->to('/admin/tariffs')->with('success', 'Berhasil mengubah data');
    }
    public function destroy(int $tariffId): ResponseInterface
    {
        if ($this->tariffModel->delete($tariffId)) {
            return redirect()->to('/admin/tariffs')->with('success', 'Berhasil menghapus data');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data');
    }
}
