<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\TravelExpenseCalculator;
use CodeIgniter\HTTP\ResponseInterface;

class TravelRequestController extends BaseController
{

    protected $travelRequestModel;
    protected $travelExpenseModel;
    protected $employeeModel;
    protected $signatoryModel;
    protected $tariffModel;

    public function __construct()
    {
        $this->travelRequestModel = model('TravelRequestModel');
        $this->travelExpenseModel = model('TravelExpenseModel');
        $this->employeeModel = model('EmployeeModel');
        $this->signatoryModel = model('SignatoriesModel');
        $this->tariffModel = model('TariffModel');
    }

    public function index(): string|ResponseInterface
    {
        $travelRequests = $this->travelRequestModel->getAllWithEmployee();

        // Attach members to each request
        foreach ($travelRequests as &$req) {
            $req->members = $this->travelExpenseModel->getByRequestWithEmployee($req->id);
        }

        $data = [
            'title' => 'Travel Request',
            'travelRequests' => $travelRequests
        ];
        return view('admin/travel/index', $data);
    }

    public function create(): string|ResponseInterface
    {
        $data = [
            'title' => 'Tambah Travel Request',
            'employees' => $this->employeeModel->findAll(),
            'signatories' => $this->signatoryModel->getAllWithEmployee(),
            'tariffs' => $this->tariffModel->findAll()
        ];
        return view('admin/travel/create', $data);
    }

    public function store(): ResponseInterface
    {
        // Get the current employee ID based on the logged-in user
        $userId = auth()->id();
        $employee = $this->employeeModel->where('user_id', $userId)->first();
        
        if (!$employee) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda belum ditautkan dengan data pegawai. Silakan hubungi admin.');
        }

        $dataRequest = [
            'employee_id'          => $employee['id'],
            'no_surat_tugas'       => $this->request->getPost('no_surat_tugas') ?: null,
            'tgl_surat_tugas'      => $this->request->getPost('tgl_surat_tugas') ?: null,
            'no_sppd'              => $this->request->getPost('no_sppd') ?: null,
            'tgl_sppd'             => $this->request->getPost('tgl_sppd') ?: null,
            'mak'                  => $this->request->getPost('mak') ?: null,
            'purpose'              => $this->request->getPost('purpose'),
            'transportation_type'  => $this->request->getPost('transportation_type'),
            'origin'               => $this->request->getPost('origin'),
            'destination'          => $this->request->getPost('destination'),
            'destination_province' => $this->request->getPost('destination_province'),
            'destination_city'     => $this->request->getPost('destination_city') ?: null,
            'departure_date'       => $this->request->getPost('departure_date'),
            'return_date'          => $this->request->getPost('return_date'),
            'duration_days'        => $this->request->getPost('duration_days'),
            'signatory_ppk_id'     => $this->request->getPost('signatory_ppk_id'),
            'signatory_kpa_id'     => $this->request->getPost('signatory_kpa_id'),
            'status'               => $this->request->getPost('action') === 'submit' ? 'pending' : 'draft',
        ];

        // Attempt to insert the travel request (model validation runs automatically)
        $result = $this->travelRequestModel->insert($dataRequest);
        if ($result === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat pengajuan: ' . implode(', ', $this->travelRequestModel->errors()));
        }

        $requestId = $this->travelRequestModel->insertID();

        // Process member expenses
        $memberIds = $this->request->getPost('members');
        $calculator = new TravelExpenseCalculator();
        $totalBudgetAll = 0;

        $errors = [];

        if (!empty($memberIds) && is_array($memberIds)) {
            foreach ($memberIds as $memberId) {
                $employee = $this->employeeModel->find($memberId);
                if (!$employee) continue;

                $tingkatBiaya = $this->getTingkatBiayaFromGolongan($employee['pangkat_golongan'] ?? '');

                $biaya = [
                    'uang_harian'       => 0,
                    'uang_representasi' => 0,
                    'penginapan'        => 0,
                    'tiket'             => 0,
                    'transport_darat'   => 0,
                    'transport_lokal'   => 0,
                    'total_biaya'       => 0,
                    'tariff_id'         => null
                ];

                try {
                    $biaya = $calculator->calculate(
                        $dataRequest['destination_province'],
                        $dataRequest['destination_city'] ?? null,
                        $tingkatBiaya,
                        (int) $dataRequest['duration_days']
                    );
                } catch (\Exception $e) {
                    $errors[] = $employee['name'] . ' - ' . $e->getMessage();
                }

                $dataExpense = [
                    'travel_request_id' => $requestId,
                    'employee_id'       => $memberId,
                    'uang_harian'       => $biaya['uang_harian'],
                    'uang_representasi' => $biaya['uang_representasi'],
                    'penginapan'        => $biaya['penginapan'],
                    'tiket'             => $biaya['tiket'],
                    'transport_darat'   => $biaya['transport_darat'],
                    'transport_lokal'   => $biaya['transport_lokal'],
                    'total_biaya'       => $biaya['total_biaya'],
                    'tariff_id'         => $biaya['tariff_id']
                ];

                $this->travelExpenseModel->insert($dataExpense);
                $totalBudgetAll += $biaya['total_biaya'];
            }
        }

        // Update total budget on the travel request
        $this->travelRequestModel->update($requestId, ['total_budget' => $totalBudgetAll]);

        $successMsg = $dataRequest['status'] === 'pending'
            ? 'Pengajuan perjalanan dinas berhasil dibuat dan langsung diajukan.'
            : 'Pengajuan perjalanan dinas berhasil disimpan sebagai draft.';

        if (!empty($errors)) {
            return redirect()->to('/admin/travel')->with('warning', $successMsg . '<br>Tetapi ada tarif yang belum diatur:<br>' . implode('<br>', $errors));
        }

        return redirect()->to('/admin/travel')->with('success', $successMsg);
    }

    public function show(int $id): string|ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);

        if (!$travelRequest) {
            return redirect()->to('/admin/travel')->with('error', 'Data tidak ditemukan.');
        }

        // Get members with employee details
        $members = $this->travelExpenseModel->getByRequestWithEmployee($id);

        // Get signatories details if available
        $ppk = $travelRequest->signatory_ppk_id ? $this->signatoryModel->find($travelRequest->signatory_ppk_id) : null;
        $kpa = $travelRequest->signatory_kpa_id ? $this->signatoryModel->find($travelRequest->signatory_kpa_id) : null;
        
        // Attach related employee data to signatory for UI display
        if ($ppk) {
            $empPpk = $this->employeeModel->find($ppk->employee_id);
            $ppk->employee_name = $empPpk['name'] ?? 'Unknown';
            $ppk->nip = $empPpk['nip'] ?? $ppk->nip;
        }
        
        if ($kpa) {
            $empKpa = $this->employeeModel->find($kpa->employee_id);
            $kpa->employee_name = $empKpa['name'] ?? 'Unknown';
            $kpa->nip = $empKpa['nip'] ?? $kpa->nip;
        }

        $data = [
            'title' => 'Detail Travel Request',
            'travelRequest' => $travelRequest,
            'members' => $members,
            'ppk' => $ppk,
            'kpa' => $kpa
        ];
        return view('admin/travel/show', $data);
    }

    public function edit(int $id): string|ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);

        $data = [
            'title' => 'Ubah Travel Request',
            'travelRequest' => $travelRequest,
            'travelExpenses' => $this->travelExpenseModel->where('travel_request_id', $id)->findAll(),
            'employees' => $this->employeeModel->findAll(),
            'signatories' => $this->signatoryModel->getAllWithEmployee(),
            'tariffs' => $this->tariffModel->findAll()
        ];
        return view('admin/travel/edit', $data);
    }

    public function update(int $id): ResponseInterface
    {
        $currentRequest = $this->travelRequestModel->find($id);
        if (!$currentRequest) {
            return redirect()->to('/admin/travel')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'employee_id'          => $currentRequest->employee_id, // Retain the original owner
            'no_surat_tugas'       => $this->request->getPost('no_surat_tugas') ?: null,
            'tgl_surat_tugas'      => $this->request->getPost('tgl_surat_tugas') ?: null,
            'no_sppd'              => $this->request->getPost('no_sppd') ?: null,
            'tgl_sppd'             => $this->request->getPost('tgl_sppd') ?: null,
            'mak'                  => $this->request->getPost('mak') ?: null,
            'purpose'              => $this->request->getPost('purpose'),
            'transportation_type'  => $this->request->getPost('transportation_type'),
            'origin'               => $this->request->getPost('origin'),
            'destination'          => $this->request->getPost('destination'),
            'destination_province' => $this->request->getPost('destination_province'),
            'destination_city'     => $this->request->getPost('destination_city') ?: null,
            'departure_date'       => $this->request->getPost('departure_date'),
            'return_date'          => $this->request->getPost('return_date'),
            'duration_days'        => $this->request->getPost('duration_days'),
            'signatory_ppk_id'     => $this->request->getPost('signatory_ppk_id'),
            'signatory_kpa_id'     => $this->request->getPost('signatory_kpa_id'),
        ];

        // Ensure we only process 'action' if the status is currently 'draft'
        // If it's already 'pending' or 'approved', we don't automatically downgrade it to 'draft' 
        // unless they explicitly press a "SImpan Draft" conditionally rendered.
        // For simplicity, let's respect the action but prevent going back from approved to draft without verification
        if ($currentRequest->status === 'draft' && $this->request->getPost('action') === 'submit') {
             $data['status'] = 'pending';
        }

        $result = $this->travelRequestModel->update($id, $data);
        if ($result === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengubah pengajuan: ' . implode(', ', $this->travelRequestModel->errors()));
        }

        // Delete old expenses and recalculate
        $this->travelExpenseModel->where('travel_request_id', $id)->delete();

        $memberIds = $this->request->getPost('members');
        $calculator = new TravelExpenseCalculator();
        $totalBudgetAll = 0;

        $errors = [];

        if (!empty($memberIds) && is_array($memberIds)) {
            foreach ($memberIds as $memberId) {
                $employee = $this->employeeModel->find($memberId);
                if (!$employee) continue;

                $tingkatBiaya = $this->getTingkatBiayaFromGolongan($employee['pangkat_golongan'] ?? '');

                $biaya = [
                    'uang_harian'       => 0,
                    'uang_representasi' => 0,
                    'penginapan'        => 0,
                    'tiket'             => 0,
                    'transport_darat'   => 0,
                    'transport_lokal'   => 0,
                    'total_biaya'       => 0,
                    'tariff_id'         => null
                ];

                try {
                    $biaya = $calculator->calculate(
                        $data['destination_province'],
                        $data['destination_city'] ?? null,
                        $tingkatBiaya,
                        (int) $data['duration_days']
                    );
                } catch (\Exception $e) {
                    $errors[] = $employee['name'] . ' - ' . $e->getMessage();
                }

                $dataExpense = [
                    'travel_request_id' => $id,
                    'employee_id'       => $memberId,
                    'uang_harian'       => $biaya['uang_harian'],
                    'uang_representasi' => $biaya['uang_representasi'],
                    'penginapan'        => $biaya['penginapan'],
                    'tiket'             => $biaya['tiket'],
                    'transport_darat'   => $biaya['transport_darat'],
                    'transport_lokal'   => $biaya['transport_lokal'],
                    'total_biaya'       => $biaya['total_biaya'],
                    'tariff_id'         => $biaya['tariff_id']
                ];

                $this->travelExpenseModel->insert($dataExpense);
                $totalBudgetAll += $biaya['total_biaya'];
            }
        }

        $this->travelRequestModel->update($id, ['total_budget' => $totalBudgetAll]);

        $successMsg = (isset($data['status']) && $data['status'] === 'pending')
            ? 'Data berhasil diubah dan langsung diajukan.'
            : 'Data berhasil diubah.';

        if (!empty($errors)) {
            return redirect()->to('/admin/travel')->with('warning', $successMsg . '<br>Tetapi ada tarif yang belum diatur:<br>' . implode('<br>', $errors));
        }

        return redirect()->to('/admin/travel')->with('success', $successMsg);
    }

    public function destroy(int $id): ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/admin/travel')->with('error', 'Data tidak ditemukan.');
        }

        // Delete expenses first if not cascaded, though it's CASCADE in DB usually.
        $this->travelExpenseModel->where('travel_request_id', $id)->delete();
        $this->travelRequestModel->delete($id);

        return redirect()->to('/admin/travel')->with('success', 'Pengajuan perjalanan dinas berhasil dihapus.');
    }

    public function submit(int $id): ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/admin/travel')->with('error', 'Data tidak ditemukan.');
        }

        if ($travelRequest->status !== 'draft') {
            return redirect()->to('/admin/travel/' . $id)->with('error', 'Hanya pengajuan berstatus draft yang dapat diajukan.');
        }

        $this->travelRequestModel->update($id, ['status' => 'pending']);

        return redirect()->to('/admin/travel/' . $id)->with('success', 'Pengajuan berhasil diajukan dan sedang menunggu verifikasi.');
    }

    public function cancel(int $id): ResponseInterface
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->to('/admin/travel')->with('error', 'Data tidak ditemukan.');
        }

        if ($travelRequest->status !== 'pending') {
            return redirect()->to('/admin/travel/' . $id)->with('error', 'Hanya pengajuan berstatus pending yang dapat dibatalkan.');
        }

        $this->travelRequestModel->update($id, ['status' => 'draft']);

        return redirect()->to('/admin/travel/' . $id)->with('success', 'Pengajuan berhasil dibatalkan dan kembali menjadi draft.');
    }

    /**
     * API Endpoint for fetching employees with filter
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

        // Limit results to avoid huge payloads
        $employees = $builder->limit(50)->get()->getResultArray();

        return $this->response->setJSON($employees);
    }

    /**
     * API Endpoint for real-time tariff checking
     */
    public function checkTariff(): ResponseInterface
    {
        $province = $this->request->getPost('province');
        $city = $this->request->getPost('city');
        $memberIds = $this->request->getPost('members');

        // Sometimes JS sends arrays differently depending on FormData vs JSON
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
            $employee = $this->employeeModel->find($memberId);
            if (!$employee) continue;

            $tingkatBiaya = $this->getTingkatBiayaFromGolongan($employee['pangkat_golongan'] ?? '');

            try {
                // We use duration=1 just to check existence of tariff
                $biaya = $calculator->calculate($province, $city, $tingkatBiaya, 1);
                $success[] = [
                    'name' => $employee['name'],
                    'tingkat_biaya' => $tingkatBiaya
                ];
            } catch (\Exception $e) {
                // If the calculator throws an Exception, the tariff is missing
                $missing[] = [
                    'name' => $employee['name'],
                    'tingkat_biaya' => $tingkatBiaya,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'missing_tariffs' => $missing,
            'success_tariffs' => $success
        ]);
    }

    /**
     * Helper mapping Pangkat/Golongan ke Tingkat Biaya Perjadin
     */
    private function getTingkatBiayaFromGolongan(string $golongan): string
    {
        $golongan = strtoupper(trim($golongan));

        // Pangkat/Golongan biasanya berformat: "Penata (III/c)" atau sekedar "III/c"
        // Kita cukup mencari adanya romawi I, II, III, atau IV.

        if (strpos($golongan, 'IV') !== false) {
            return 'A';
        } elseif (strpos($golongan, 'III') !== false) {
            return 'B';
        } elseif (strpos($golongan, 'II') !== false && strpos($golongan, 'III') === false) { // Handle II but not III
            return 'C';
        } elseif (strpos($golongan, 'I') !== false && strpos($golongan, 'II') === false && strpos($golongan, 'III') === false && strpos($golongan, 'IV') === false) {
            return 'D';
        }

        // Default keamanan jika format tak terbaca
        return 'C';
    }
}
