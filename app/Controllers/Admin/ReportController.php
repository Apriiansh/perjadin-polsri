<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TravelRequestModel;
use App\Models\TravelMemberModel;
use App\Models\TravelExpenseModel;
use App\Libraries\Templates\SpjPdfTemplate;
use App\Models\SignatoriesModel;
use ZipArchive;

class ReportController extends BaseController
{
    protected $travelRequestModel;
    protected $travelMemberModel;
    protected $travelExpenseModel;
    protected $signatoryModel;

    public function __construct()
    {
        $this->travelRequestModel = new TravelRequestModel();
        $this->travelMemberModel = new TravelMemberModel();
        $this->travelExpenseModel = new TravelExpenseModel();
        $this->signatoryModel = new SignatoriesModel();
        helper(['terbilang', 'url']);
    }

    /**
     * List all reports (Travel Requests that are completed/active)
     */
    public function index()
    {
        $data = [
            'title'   => 'Laporan Perjalanan Dinas',
            'travels' => $this->travelRequestModel
                ->select('travel_requests.*, employees.name as creator_name')
                ->join('employees', 'employees.user_id = travel_requests.created_by')
                ->where('travel_requests.status', 'completed')
                ->orderBy('travel_requests.created_at', 'DESC')
                ->findAll(),
        ];

        return view('travel/reports', $data);
    }

    /**
     * Main action: Generate discrete SPJ PDFs + Attachments into a structured ZIP
     */
    public function downloadSpjBundle(int $id)
    {
        $travelRequest = $this->travelRequestModel->find($id);
        if (!$travelRequest) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $members = $this->travelExpenseModel->getByRequestWithMember($id);

        // Resolve Signatories
        $ppk = $this->signatoryModel->select('signatories.*, employees.name as employee_name, employees.nip')
            ->join('employees', 'employees.id = signatories.employee_id')
            ->like('signatories.jabatan', 'PPK')
            ->where('signatories.is_active', 1)->first();

        $bpp = $this->signatoryModel->select('signatories.*, employees.name as employee_name, employees.nip')
            ->join('employees', 'employees.id = signatories.employee_id')
            ->like('signatories.jabatan', 'Bendahara Pengeluaran Pembantu')
            ->where('signatories.is_active', 1)->first();

        $bendahara = $this->signatoryModel->select('signatories.*, employees.name as employee_name, employees.nip')
            ->join('employees', 'employees.id = signatories.employee_id')
            ->like('signatories.jabatan', 'Bendahara Pengeluaran')
            ->where('signatories.is_active', 1)->first();

        $spjTemplate = new SpjPdfTemplate();

        // 2. Prepare ZIP
        $zip = new ZipArchive();
        $zipFileName = 'SPJ_Bundle_' . $id . '_' . date('YmdHis') . '.zip';
        $zipPath = WRITEPATH . 'uploads/' . $zipFileName;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            // ── A. INDIVIDUAL DOCUMENTS (PER MEMBER) ───────────────────────
            foreach ($members as $member) {
                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $member->employee_name);
                $memberFolder = $cleanName . '/';

                // 1. SPD
                $sppdContent = $spjTemplate->generateSppd($travelRequest, $member, $ppk);
                $zip->addFromString($memberFolder . '1_SPD_' . $cleanName . '.pdf', $sppdContent);

                // 2. Rincian Biaya & Kuitansi
                $rincianContent = $spjTemplate->generateRincian($travelRequest, $member, $ppk, $bpp, $bendahara);
                $zip->addFromString($memberFolder . '2_Rincian_Biaya_Kuitansi_' . $cleanName . '.pdf', $rincianContent);

                // 3. Surat Pernyataan
                $pernyataanContent = $spjTemplate->generatePernyataan($travelRequest, $member, $ppk);
                $zip->addFromString($memberFolder . '3_Surat_Pernyataan_' . $cleanName . '.pdf', $pernyataanContent);

                // 4. Documentation & Attachments (Organized into subfolders)
                $db = \Config\Database::connect();
                $completenessItems = $db->table('travel_completeness')
                    ->where('member_id', $member->travel_member_id)
                    ->get()->getResult();

                foreach ($completenessItems as $item) {
                    $cleanItemName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $item->item_name);
                    $itemFolder = $memberFolder . 'Dokumentasi/' . $cleanItemName . '/';

                    $files = $db->table('travel_completeness_files')
                        ->where('completeness_id', $item->id)
                        ->get()->getResult();

                    foreach ($files as $file) {
                        $filePath = WRITEPATH . 'uploads/' . $file->file_path;
                        if (file_exists($filePath)) {
                            $zip->addFile($filePath, $itemFolder . $file->original_name);
                        }
                    }
                }
            }

            // ── B. COLLECTIVE DOCUMENTS ────────────────────────────────────
            $collectiveFolder = 'Laporan/';

            // 1. Daftar Kontrol
            $kontrolContent = $spjTemplate->generateKontrol($travelRequest, $members, $ppk, $bpp);
            $zip->addFromString($collectiveFolder . '1_Daftar_Kontrol_Pembayaran.pdf', $kontrolContent);

            // 2. Daftar Nominatif
            if (count($members) > 1) {
                $nominatifContent = $spjTemplate->generateNominatif($travelRequest, $members, $ppk, $bpp);
                $zip->addFromString($collectiveFolder . '2_Daftar_Nominatif.pdf', $nominatifContent);
            }

            // 3. Shared Documentation (Not assigned to specific members)
            $db = \Config\Database::connect();
            $sharedItems = $db->table('travel_completeness')
                ->where('travel_request_id', $id)
                ->where('member_id', null)
                ->get()->getResult();

            foreach ($sharedItems as $item) {
                $cleanItemName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $item->item_name);
                $itemFolder = $collectiveFolder . 'Dokumentasi/' . $cleanItemName . '/';

                $files = $db->table('travel_completeness_files')
                    ->where('completeness_id', $item->id)
                    ->get()->getResult();

                foreach ($files as $file) {
                    $filePath = WRITEPATH . 'uploads/' . $file->file_path;
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, $itemFolder . $file->original_name);
                    }
                }
            }

            $zip->close();

            // Stream ZIP to user
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);

            // Delete temporary ZIP after streaming
            @unlink($zipPath);
            exit;
        }

        return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
    }
}
