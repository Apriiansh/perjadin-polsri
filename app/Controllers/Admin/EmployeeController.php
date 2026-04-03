<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\RedirectResponse;

class EmployeeController extends BaseController
{
    private EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
    }

    public function index(): string
    {
        $employees = $this->employeeModel
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('admin/employees/index', [
            'title' => 'Manage Pegawai',
            'employees' => $employees,
        ]);
    }

    public function sync(): RedirectResponse
    {
        try {
            // 1. Hubungkan ke Database PolsriPay
            $dbPolsripay = \Config\Database::connect('polsripay');
            
            // 2. Ambil data pegawai lengkap dengan nama jurusannya (Join)
            $query = $dbPolsripay->table('pegawai')
                ->select('pegawai.*, jurusan.nama_jurusan')
                ->join('jurusan', 'jurusan.id_jurusan = pegawai.id_jurusan', 'left')
                ->get();
                
            $rows = $query->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Gagal konek ke DB Polsripay: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Sinkronisasi gagal. Pastikan konfigurasi database "polsripay" di .env sudah benar.');
        }

        if (empty($rows)) {
            return redirect()->back()->with('error', 'Data pegawai di Database Polsripay tidak ditemukan.');
        }

        $now = date('Y-m-d H:i:s');
        $syncedCount = 0;

        foreach ($rows as $row) {
            // Gunakan id_pegawai (UUID) sebagai identifier unik dari PolsriPay
            $sourceId = $row['id_pegawai'] ?? null;
            $nip      = trim((string)($row['nip'] ?? ''));
            $name     = trim((string)($row['nama'] ?? ''));

            if (!$sourceId || $nip === '' || $name === '') {
                continue;
            }

            $mapped = $this->mapEmployeeRow($row);

            // Cari apakah pegawai sudah ada di database Perjadin (berdasarkan ID asal atau NIP)
            $existing = $this->employeeModel
                ->groupStart()
                    ->where('api_employee_id', $sourceId)
                    ->orWhere('nip', $nip)
                ->groupEnd()
                ->first();

            $data = [
                'api_employee_id'  => $sourceId,
                'nik'              => $mapped['nik'],
                'nip'              => $nip,
                'nuptk'            => $mapped['nuptk'],
                'name'             => $name,
                'pangkat_golongan' => $mapped['pangkat_golongan'],
                'jabatan'          => $mapped['jabatan'],
                'jafun'            => $mapped['jafun'],
                'rekening_bank'    => $mapped['rekening_bank'],
                'id_jurusan'       => $mapped['id_jurusan'],
                'nama_jurusan'     => $mapped['nama_jurusan'],
                'status'           => $mapped['status'],
                'synced_at'        => $now,
                'api_created_at'   => $row['created_at'] ?? null,
                'api_updated_at'   => $row['updated_at'] ?? null,
            ];

            if ($existing === null) {
                $this->employeeModel->insert($data);
            } else {
                $this->employeeModel->update((int) $existing['id'], $data);
            }

            $syncedCount++;
        }

        return redirect()->back()->with('success', "Sinkronisasi Berhasil. {$syncedCount} data pegawai telah diperbarui dari PolsriPay.");
    }

    /**
     * Memetakan baris database PolsriPay ke struktur Perjadin
     */
    private function mapEmployeeRow(array $row): array
    {
        return [
            'nik'              => !empty($row['nik']) ? trim((string)$row['nik']) : null,
            'nuptk'            => !empty($row['nuptk']) ? trim((string)$row['nuptk']) : null,
            'pangkat_golongan' => $row['golongan'] ?? null,
            'jabatan'          => $row['jabatan'] ?? null,
            'jafun'            => $row['jafun'] ?? null,
            'rekening_bank'    => $row['nomor_rekening'] ?? null,
            'id_jurusan'       => $row['id_jurusan'] ?? null,
            'nama_jurusan'     => $row['nama_jurusan'] ?? null,
            'status'           => (strtolower($row['status'] ?? '') === 'nonaktif') ? 'nonaktif' : 'aktif',
        ];
    }

    /**
     * Logika sederhana untuk menentukan Tingkat Biaya Perjalanan Dinas
     * (Bisa disesuaikan dengan aturan kampus)
     */
    private function determineTingkatBiaya(string $jabatan, string $golongan): ?string
    {
        $jab = strtolower($jabatan);
        if (str_contains($jab, 'direktur') || str_contains($jab, 'wadir')) return 'A';
        if (str_contains($jab, 'ketua') || str_contains($jab, 'kepala')) return 'B';
        
        $gol = substr($golongan, 0, 1);
        if ($gol === '4') return 'B';
        if ($gol === '3') return 'C';
        
        return 'D';
    }
}
