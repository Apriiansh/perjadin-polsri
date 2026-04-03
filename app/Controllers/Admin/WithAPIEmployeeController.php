<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\PolsriApiService;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\RedirectResponse;

class WithAPIEmployeeController extends BaseController
{
    private EmployeeModel $employeeModel;
    private PolsriApiService $apiService;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
        $this->apiService = new PolsriApiService();
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
        $payload = $this->apiService->fetchEmployees();

        if ($payload === []) {
            return redirect()->back()->with('error', 'Sinkronisasi gagal. Periksa API URL/key atau koneksi jaringan.');
        }

        $rows = $this->normalizeEmployees($payload);

        if ($rows === []) {
            return redirect()->back()->with('error', 'Data API kosong atau format tidak dikenali.');
        }

        $now = date('Y-m-d H:i:s');
        $syncedCount = 0;

        foreach ($rows as $row) {
            $identity = $this->resolveIdentity($row);
            if ($identity === null) {
                continue;
            }

            $mapped = $this->mapEmployeeRow($row);
            if ($mapped['nip'] === '' || $mapped['name'] === '') {
                continue;
            }

            $existing = $this->employeeModel
                ->groupStart()
                ->where('api_employee_id', $identity)
                ->orWhere('nip', $mapped['nip'])
                ->groupEnd()
                ->first();

            $data = [
                'api_employee_id' => $identity,
                'nik' => $mapped['nik'],
                'nip' => $mapped['nip'],
                'nuptk' => $mapped['nuptk'],
                'name' => $mapped['name'],
                'email' => $mapped['email'],
                'pangkat_golongan' => $mapped['pangkat_golongan'],
                'jabatan' => $mapped['jabatan'],
                'jafun' => $mapped['jafun'],
                'tingkat_biaya' => $mapped['tingkat_biaya'],
                'rekening_bank' => $mapped['rekening_bank'],
                'id_jurusan' => $mapped['id_jurusan'],
                'nama_jurusan' => $mapped['nama_jurusan'],
                'status' => $mapped['status'],
                'synced_at' => $now,
                'api_created_at' => $mapped['api_created_at'],
                'api_updated_at' => $mapped['api_updated_at'],
            ];

            if ($existing === null) {
                $this->employeeModel->insert($data);
            } else {
                $this->employeeModel->update((int) $existing['id'], $data);
            }

            $syncedCount++;
        }

        return redirect()->back()->with('success', "Sinkronisasi selesai. {$syncedCount} data diproses.");
    }

    private function normalizeEmployees(array $payload): array
    {
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        if (isset($payload['pegawai']) && is_array($payload['pegawai'])) {
            return $payload['pegawai'];
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        return [];
    }

    private function resolveIdentity(array $row): ?string
    {
        $candidates = [
            $row['id'] ?? null,
            $row['pegawai_id'] ?? null,
            $row['id_pegawai'] ?? null,
            $row['nip'] ?? null,
        ];

        foreach ($candidates as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    /**
     * @return array{nik: ?string, nip: string, nuptk: ?string, name: string, email: ?string, pangkat_golongan: ?string, jabatan: ?string, jafun: ?string, tingkat_biaya: ?string, rekening_bank: ?string, id_jurusan: ?string, nama_jurusan: ?string, status: string, api_created_at: ?string, api_updated_at: ?string}
     */
    private function mapEmployeeRow(array $row): array
    {
        $nik = trim((string) ($row['nik'] ?? $row['NIK'] ?? ''));
        $nip = trim((string) ($row['nip'] ?? $row['NIP'] ?? ''));
        $nuptk = trim((string) ($row['nuptk'] ?? ''));
        $name = trim((string) ($row['name'] ?? $row['nama'] ?? $row['nama_pegawai'] ?? ''));
        $email = $row['email'] ?? $row['mail'] ?? null;
        $pangkat = $row['pangkat_golongan'] ?? $row['golongan'] ?? null;
        $jabatan = $row['jabatan'] ?? $row['position'] ?? null;
        $jafun = $row['jafun'] ?? null;
        $rekeningBank = $row['rekening_bank'] ?? $row['nomor_rekening'] ?? null;
        $idJurusan = $row['id_jurusan'] ?? null;
        $namaJurusan = $row['nama_jurusan'] ?? null;

        $statusApi = strtolower(trim((string) ($row['status'] ?? 'aktif')));
        $status = $statusApi === 'nonaktif' ? 'nonaktif' : 'aktif';

        $apiCreatedAt = $this->normalizeDateTime($row['created_at'] ?? null);
        $apiUpdatedAt = $this->normalizeDateTime($row['updated_at'] ?? null);

        $tingkatBiayaRaw = strtoupper((string) ($row['tingkat_biaya'] ?? $row['tingkatBiaya'] ?? ''));
        $tingkatBiaya = in_array($tingkatBiayaRaw, ['A', 'B', 'C', 'D'], true) ? $tingkatBiayaRaw : null;

        return [
            'nik' => $nik !== '' ? $nik : null,
            'nip' => $nip,
            'nuptk' => $nuptk !== '' ? $nuptk : null,
            'name' => $name,
            'email' => $email !== null ? trim((string) $email) : null,
            'pangkat_golongan' => $pangkat !== null ? trim((string) $pangkat) : null,
            'jabatan' => $jabatan !== null ? trim((string) $jabatan) : null,
            'jafun' => $jafun !== null ? trim((string) $jafun) : null,
            'tingkat_biaya' => $tingkatBiaya,
            'rekening_bank' => $rekeningBank !== null ? trim((string) $rekeningBank) : null,
            'id_jurusan' => $idJurusan !== null ? trim((string) $idJurusan) : null,
            'nama_jurusan' => $namaJurusan !== null ? trim((string) $namaJurusan) : null,
            'status' => $status,
            'api_created_at' => $apiCreatedAt,
            'api_updated_at' => $apiUpdatedAt,
        ];
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $time = strtotime($value);
        if ($time === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $time);
    }
}
