<?php

namespace App\Libraries\Templates;

use Dompdf\Dompdf;
use Options;

class SpjPdfTemplate
{
    private const TRANSPORT_LABELS = [
        'udara' => 'Pesawat',
        'darat' => 'Mobil',
        'laut'  => 'Kapal',
    ];

    private $options;

    public function __construct()
    {
        $this->options = new \Dompdf\Options();
        $this->options->set('isHtml5ParserEnabled', true);
        $this->options->set('isRemoteEnabled', true);
        $this->options->set('defaultFont', 'Times New Roman');
    }

    /**
     * Common method to render HTML to PDF binary string
     */
    private function renderToPdf(string $html, string $paperSize = 'A4', string $orientation = 'portrait'): string
    {
        $dompdf = new Dompdf($this->options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paperSize, $orientation);
        $dompdf->render();
        return $dompdf->output();
    }

    /**
     * Generate SPD PDF for a single member
     */
    public function generateSppd(object $travelRequest, object $member, ?object $ppk = null): string
    {
        $data = $this->prepareSharedData($travelRequest, [$member], $ppk);
        $member->tingkatBiaya   = $this->resolveTingkat($member);
        $data['member']         = $member;
        $data['tingkatBiaya']   = $member->tingkatBiaya;
        $data['showBackPage']   = true; // Show the verification grid on the back
        $data['template']       = $this;
        $data['tglBerangkat']   = $this->formatTanggal($travelRequest->departure_date);
        $data['tglKembali']     = $this->formatTanggal($travelRequest->return_date);
        $data['tglSurat']       = $this->formatTanggal($travelRequest->created_at);

        $html = view('travel/pdf/sppd_pdf', $data);
        return $this->renderToPdf($html, 'A4', 'portrait');
    }

    /**
     * Generate Rincian Biaya & Kuitansi PDF for a single member
     */
    public function generateRincian(
        object $travelRequest, 
        object $member, 
        ?object $ppk = null, 
        ?object $bpp = null, 
        ?object $bendahara = null
    ): string {
        $data = $this->prepareSharedData($travelRequest, [$member], $ppk, $bpp, $bendahara);
        $data['member']       = $member;
        $data['terbilang']    = $this->terbilang($member->total_biaya ?? 0);
        $data['tempatTerbit'] = $travelRequest->departure_place ?: 'Palembang';
        $data['tglSurat']     = $this->formatTanggal($travelRequest->created_at);
        $data['template']     = $this;
        
        // Fetch specific expense items if available
        $db = \Config\Database::connect();
        $data['expenseItems'] = $db->table('travel_expense_items')
            ->where('travel_member_id', $member->travel_member_id)
            ->get()->getResult();

        $html = view('travel/pdf/rincian_biaya_pdf', $data);
        return $this->renderToPdf($html, 'A4', 'portrait');
    }

    /**
     * Generate Surat Pernyataan PDF for a single member
     */
    public function generatePernyataan(object $travelRequest, object $member, ?object $ppk = null): string
    {
        $data = $this->prepareSharedData($travelRequest, [$member], $ppk);
        $data['member']         = $member;
        $data['tglSuratTugas']  = $this->formatTanggal($travelRequest->departure_date);
        $data['tglTandaTangan'] = $this->formatTanggal($travelRequest->tgl_surat_tugas ?? $travelRequest->created_at);
        $data['tempatTerbit']   = $travelRequest->departure_place ?: 'Palembang';
        $data['template']       = $this;

        $html = view('travel/pdf/surat_pernyataan_pdf', $data);
        return $this->renderToPdf($html, 'A4', 'portrait');
    }

    /**
     * Generate Daftar Kontrol PDF (Collective)
     */
    public function generateKontrol(
        object $travelRequest, 
        array $members, 
        ?object $ppk = null, 
        ?object $bpp = null
    ): string {
        $data = $this->prepareSharedData($travelRequest, $members, $ppk, $bpp);
        $grandTotal = 0;
        foreach ($members as $m) $grandTotal += ($m->total_biaya ?? 0);
        $data['terbilangGrand'] = $this->terbilang($grandTotal);
        $data['tglSurat'] = $this->formatTanggal($travelRequest->departure_date);

        $html = view('travel/pdf/daftar_kontrol_pdf', $data);
        return $this->renderToPdf($html, 'A4', 'landscape');
    }

    /**
     * Generate Daftar Nominatif PDF (Collective)
     */
    public function generateNominatif(
        object $travelRequest, 
        array $members, 
        ?object $ppk = null, 
        ?object $bpp = null
    ): string {
        $data = $this->prepareSharedData($travelRequest, $members, $ppk, $bpp);
        $grandTotal = 0;
        foreach ($members as $m) $grandTotal += ($m->total_biaya ?? 0);
        $data['terbilangGrand'] = $this->terbilang($grandTotal);
        $data['tglSurat'] = $this->formatTanggal($travelRequest->departure_date);

        $html = view('travel/pdf/daftar_nominatif_pdf', $data);
        return $this->renderToPdf($html, 'A4', 'landscape');
    }

    /**
     * Generate Documentation Gallery PDF
     */
    public function generateDokumentasi(int $travelRequestId, ?object $ppk = null): string
    {
        $db = \Config\Database::connect();
        $travelRequest = $db->table('travel_requests')->where('id', $travelRequestId)->get()->getRow();
        
        // 1. Fetch Documentation Files from travel_completeness and travel_completeness_files
        $docs = $db->table('travel_completeness_files')
            ->select('travel_completeness_files.*, travel_completeness.item_name')
            ->join('travel_completeness', 'travel_completeness.id = travel_completeness_files.completeness_id')
            ->where('travel_completeness.travel_request_id', $travelRequestId)
            ->get()->getResult();

        $images = [];
        foreach ($docs as $doc) {
            $filePath = FCPATH . 'uploads/' . $doc->file_path;
            if (file_exists($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $images[] = [
                        'base64' => 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($filePath)),
                        'title'  => $doc->item_name ?: $doc->original_name
                    ];
                }
            }
        }

        $data = [
            'travelRequest' => $travelRequest,
            'ppk'           => $ppk,
            'images'        => $images
        ];

        $html = view('travel/pdf/dokumentasi_pdf', $data);
        return $this->renderToPdf($html, 'A4', 'portrait');
    }

    private function prepareSharedData($travelRequest, $members, $ppk = null, $bpp = null, $bendahara = null): array
    {
        $tujuan = $travelRequest->destination_city
            ? $travelRequest->destination_city . ', ' . $travelRequest->destination_province
            : $travelRequest->destination_province;

        $transportLabel = self::TRANSPORT_LABELS[strtolower((string) $travelRequest->transportation_type)] ?? strtoupper((string) $travelRequest->transportation_type);

        return [
            'travelRequest'  => $travelRequest,
            'members'        => $members,
            'ppk'            => $ppk,
            'bpp'            => $bpp,
            'bendahara'      => $bendahara,
            'tujuan'         => $tujuan,
            'transportLabel' => $transportLabel,
            'tglSurat'       => $this->formatTanggal($travelRequest->created_at),
            'tempatTerbit'   => $travelRequest->departure_place ?: 'Palembang',
        ];
    }

    private function imageToBase64(string $path): string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = @file_get_contents($path);
        if ($data === false) return '';
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    private function isImage(string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
    }

    public function formatTanggal(?string $date): string
    {
        if (empty($date)) return '-';
        $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $ts = strtotime($date);
        return date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }

    public function resolveTingkat(object $member): string
    {
        if (isset($member->tingkat_biaya) && !empty($member->tingkat_biaya)) {
            return $member->tingkat_biaya;
        }

        $golSrc = $member->kode_golongan ?: ($member->employee_golongan ?? '');
        if (!$golSrc) return '-';
        $gol = strtoupper($golSrc);
        if (strpos($gol, 'IV') !== false) return 'A';
        if (strpos($gol, 'III') !== false) return 'B';
        if (strpos($gol, 'II') !== false) return 'C';
        if (strpos($gol, 'I') !== false) return 'D';
        return '-';
    }

    /**
     * Indonesian "Terbilang" Helper
     */
    public function terbilang($nilai): string
    {
        $nilai = abs((float)$nilai);
        $huruf = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        $temp = "";
        
        if ($nilai < 12) {
            $temp = " " . $huruf[(int)$nilai];
        } else if ($nilai < 20) {
            $temp = $this->terbilang($nilai - 10) . " belas";
        } else if ($nilai < 100) {
            $temp = $this->terbilang($nilai / 10) . " puluh" . $this->terbilang((int)$nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . $this->terbilang($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = $this->terbilang($nilai / 100) . " ratus" . $this->terbilang((int)$nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . $this->terbilang($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = $this->terbilang($nilai / 1000) . " ribu" . $this->terbilang((int)$nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = $this->terbilang($nilai / 1000000) . " juta" . $this->terbilang((int)$nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = $this->terbilang($nilai / 1000000000) . " milyar" . $this->terbilang(fmod($nilai, 1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = $this->terbilang($nilai / 1000000000000) . " trilyun" . $this->terbilang(fmod($nilai, 1000000000000));
        }
        return $temp;
    }
}
