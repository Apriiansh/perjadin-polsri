<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 1.5cm 1cm; size: a4 portrait; }
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .page-break { page-break-after: always; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .uppercase { text-transform: uppercase; }
        .italic { font-style: italic; }
        
        /* Layout Utilities */
        .mb-5 { margin-bottom: 5px; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }
        .mt-30 { margin-top: 30px; }
        .mt-50 { margin-top: 50px; }
        
        /* Info Row logic */
        .info-row { margin-bottom: 4px; overflow: hidden; }
        .info-label { display: inline-block; width: 160px; vertical-align: top; }
        .info-colon { display: inline-block; width: 15px; vertical-align: top; }
        .info-value { display: inline-block; width: 350px; vertical-align: top; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        td, th { vertical-align: top; word-wrap: break-word; padding: 3px 5px; }
        table.bordered th, table.bordered td { border: 1px solid #000; padding: 6px; }
        
        .col-no { width: 35px; text-align: center; }
        .col-detail { width: auto; }
        .col-cur { width: 35px; border-right: none !important; }
        .col-val { width: 110px; text-align: right; border-left: none !important; }
        .col-note { width: 120px; }

        /* Signature logic */
        .sig-container { width: 100%; margin-top: 30px; }
        .sig-box { display: inline-block; width: 48%; vertical-align: top; }
        .sig-spacer { height: 70px; }

        .divider { border-bottom: 1.5px solid #000; margin: 25px 0; }
        
        /* Calc Table */
        .calc-row { margin-bottom: 5px; }
        .calc-label { display: inline-block; width: 220px; }
        .calc-val-box { display: inline-block; width: 150px; border-bottom: 1px solid #000; text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <div class="document-page">
        <!-- SECTION 1: RINCIAN BIAYA -->
        <h3 class="text-center bold underline uppercase mb-20">Rincian Biaya Perjalanan Dinas</h3>

        <div class="header-info mb-20">
            <div class="info-row">
                <span class="info-label bold">Lampiran SPPD Nomor</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?= esc($member->no_sppd ?: '          /SPPD/BLU/' . ($travelRequest->tahun_anggaran ?: date('Y'))) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label bold">Tanggal</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?= !empty($member->tgl_sppd) ? date('d F Y', strtotime($member->tgl_sppd)) : $tglSurat ?></span>
            </div>
        </div>

        <table class="bordered">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-detail">Perincian Biaya</th>
                    <th colspan="2" style="width: 145px;">Jumlah</th>
                    <th class="col-note">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalBiaya = $member->total_biaya ?? 0;
                $i = 1;
                if (!empty($expenseItems)): 
                    foreach ($expenseItems as $item): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td><?= esc($item->item_name) ?></td>
                            <td class="col-cur">Rp.</td>
                            <td class="col-val"><?= number_format($item->amount, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php endforeach; 
                else: 
                    $dailyRate = ($travelRequest->duration_days > 0 && ($member->uang_harian ?? 0) > 0) ? $member->uang_harian / $travelRequest->duration_days : 0;
                    if (($member->uang_harian ?? 0) > 0): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>Uang Harian <?= $travelRequest->duration_days ?> hari @ Rp <?= number_format($dailyRate, 0, ',', '.') ?></td>
                            <td class="col-cur">Rp.</td>
                            <td class="col-val"><?= number_format($member->uang_harian, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php endif; 
                    if (($member->tiket ?? 0) > 0): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>Tiket <?= esc($travelRequest->departure_place ?: 'Palembang') ?>-<?= esc($tujuan) ?> (PP)</td>
                            <td class="col-cur">Rp.</td>
                            <td class="col-val"><?= number_format($member->tiket, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php endif;
                    if (($member->penginapan ?? 0) > 0): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>Penginapan</td>
                            <td class="col-cur">Rp.</td>
                            <td class="col-val"><?= number_format($member->penginapan, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php endif;
                    if (($member->transport_darat ?? 0) > 0): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>Transport Darat/taksi (PP)</td>
                            <td class="col-cur">Rp.</td>
                            <td class="col-val"><?= number_format($member->transport_darat, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php endif;
                    if (($member->transport_lokal ?? 0) > 0): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>Transport Lokal/taksi (PP)</td>
                            <td class="col-cur">Rp.</td>
                            <td class="col-val"><?= number_format($member->transport_lokal, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php endif;
                    if (($member->uang_representasi ?? 0) > 0): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>Uang Representasi</td>
                            <td class="col-cur">Rp.</td>
                            <td class="col-val"><?= number_format($member->uang_representasi, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php endif;
                endif; ?>
                <tr>
                    <td colspan="2" class="bold text-center">J U M L A H</td>
                    <td class="bold col-cur">Rp.</td>
                    <td class="bold col-val"><?= number_format($totalBiaya, 0, ',', '.') ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="mt-10 mb-20">
            <span class="bold">Terbilang: </span>
            <span class="bold italic"><?= ucfirst(trim($terbilang)) ?> Rupiah,-</span>
        </div>

        <div class="sig-container">
            <div class="sig-box">
                <br>Telah dibayar sejumlah<br>
                <span class="bold">Rp. <?= number_format($totalBiaya, 0, ',', '.') ?> ,-</span><br><br>
                Bendahara Pengeluaran Pembantu,<br>
                <div class="sig-spacer"></div>
                <span class="bold underline"><?= esc($bpp ? $bpp->employee_name : '___________________________') ?></span><br>
                NIP. <?= esc($bpp ? ($bpp->nip ?: '-') : '___________________________') ?>
            </div>
            <div class="sig-box" style="text-align: left; padding-left: 20px;">
                <?= esc($tempatTerbit) ?>, <?= $tglSurat ?><br>
                Telah menerima jumlah uang sebesar<br>
                <span class="bold">Rp. <?= number_format($totalBiaya, 0, ',', '.') ?> ,-</span><br><br>
                Yang menerima,<br>
                <div class="sig-spacer"></div>
                <span class="bold underline"><?= esc($member->employee_name) ?></span><br>
                NIP. <?= esc($member->employee_nip ?: '-') ?>
            </div>
        </div>

        <div class="divider"></div>

        <!-- SECTION 2: PERHITUNGAN SPPD RAMPUNG -->
        <h3 class="text-center bold underline uppercase mb-20">Perhitungan SPPD Rampung</h3>

        <div style="width: 80%; margin: 0 auto;">
            <div class="calc-row">
                <span class="calc-label">Ditetapkan sejumlah</span>
                <span class="info-colon">:</span>
                <span>Rp.</span>
                <span class="calc-val-box"><?= number_format($totalBiaya, 0, ',', '.') ?></span>
                <span>,-</span>
            </div>
            <div class="calc-row">
                <span class="calc-label">Yang telah dibayar semula</span>
                <span class="info-colon">:</span>
                <span>Rp.</span>
                <span class="calc-val-box"><?= number_format($totalBiaya, 0, ',', '.') ?></span>
                <span>,-</span>
            </div>
            <div class="calc-row">
                <span class="calc-label">Sisa kurang / lebih</span>
                <span class="info-colon">:</span>
                <span>Rp.</span>
                <span class="calc-val-box" style="border-bottom: none;">NIHIL</span>
                <span>,-</span>
            </div>
        </div>

        <div class="sig-container">
            <div class="sig-box"></div>
            <div class="sig-box text-center">
                Pejabat Pembuat Komitmen,<br>
                <div class="sig-spacer"></div>
                <span class="bold underline"><?= esc($ppk ? $ppk->employee_name : '___________________________') ?></span><br>
                NIP. <?= esc($ppk ? ($ppk->nip ?: '-') : '___________________________') ?>
            </div>
        </div>

        <div class="mt-20" style="font-size: 10pt;">
            <div class="info-row">
                <span style="display:inline-block; width: 100px;">Beban MAK</span>
                <span class="info-colon">:</span>
                <span><?= esc($travelRequest->mak ?: '-') ?></span>
            </div>
            <div class="info-row">
                <span style="display:inline-block; width: 100px;">Tahun Anggaran</span>
                <span class="info-colon">:</span>
                <span><?= esc($travelRequest->tahun_anggaran ?: date('Y')) ?></span>
            </div>
        </div>
        
        <div class="page-break"></div>

        <!-- SECTION 3: KUITANSI -->
        <h3 class="text-center bold underline uppercase mt-20 mb-30">K U I T A N S I</h3>

        <div class="kuitansi-content">
            <div class="info-row mb-10">
                <span class="info-label">Sudah Terima dari</span>
                <span class="info-colon">:</span>
                <span class="info-value bold">Bendaharawan Politeknik Negeri Sriwijaya</span>
            </div>
            <div class="info-row mb-10">
                <span class="info-label">Uang Sebesar</span>
                <span class="info-colon">:</span>
                <span class="info-value">
                    <span class="bold" style="padding: 5px 10px; border: 1.5px solid #000; background-color: #f9f9f9; display: inline-block;">
                        Rp. <?= number_format($totalBiaya, 0, ',', '.') ?> ,-
                    </span>
                </span>
            </div>
            <div class="info-row mb-10">
                <span class="info-label">Terbilang</span>
                <span class="info-colon">:</span>
                <span class="info-value bold italic">" <?= ucfirst(trim($terbilang)) ?> Rupiah "</span>
            </div>
            <div class="info-row mb-5">
                <span class="info-label">Untuk Pembayaran</span>
                <span class="info-colon">:</span>
                <span class="info-value">Biaya perjalanan dinas berdasarkan SPPD <?= esc($travelRequest->budget_burden_by ?: 'Direktur Politeknik Negeri Sriwijaya') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label" style="padding-left: 20px; width: 140px;">Nomor</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?= esc($member->no_sppd ?: '          /SPPD/BLU/' . ($travelRequest->tahun_anggaran ?: date('Y'))) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label" style="padding-left: 20px; width: 140px;">Tanggal</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?= !empty($member->tgl_sppd) ? date('d F Y', strtotime($member->tgl_sppd)) : $tglSurat ?></span>
            </div>
            <div class="info-row mt-10">
                <span class="info-label">Untuk Perjalanan Dinas</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?= esc($travelRequest->departure_place ?: 'Palembang') ?> ke <?= esc($tujuan) ?></span>
            </div>
        </div>

        <div class="sig-container mt-50">
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; width: 33%; vertical-align: top;">
                    <br>Bendahara Pengeluaran,<br>
                    <div class="sig-spacer"></div>
                    <span class="bold underline"><?= esc($bendahara ? $bendahara->employee_name : '___________________________') ?></span><br>
                    NIP. <?= esc($bendahara ? ($bendahara->nip ?: '-') : '___________________________') ?>
                </div>
                <div style="display: table-cell; width: 34%; vertical-align: top; text-align: center;">
                    Setuju dibayar,<br>
                    PPK,<br>
                    <div class="sig-spacer"></div>
                    <span class="bold underline"><?= esc($ppk ? $ppk->employee_name : '___________________________') ?></span><br>
                    NIP. <?= esc($ppk ? ($ppk->nip ?: '-') : '___________________________') ?>
                </div>
                <div style="display: table-cell; width: 33%; vertical-align: top; text-align: left; padding-left: 30px;">
                    <?= esc($tempatTerbit) ?>, <?= $tglSurat ?><br>
                    Yang menerima,<br>
                    <div class="sig-spacer"></div>
                    <span class="bold underline"><?= esc($member->employee_name) ?></span><br>
                    NIP. <?= esc($member->employee_nip ?: '-') ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
