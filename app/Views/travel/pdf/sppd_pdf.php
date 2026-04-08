<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SPD - <?= esc($travelRequest->no_surat_tugas ?: 'Dokumen') ?></title>
    <style>
        @page {
            margin: 1.5cm 2.0cm 1.5cm 2.5cm;
            /* T R B L */
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-underline {
            text-decoration: underline;
        }

        .text-justify {
            text-align: justify;
        }

        .spd-title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            margin-bottom: 20px;
        }

        /* ── TABLE SPD (PAGE 1) ── */
        .spd-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }

        .spd-table td {
            border: 1px solid black;
            padding: 5px 8px;
            vertical-align: top;
        }

        .no-col {
            width: 30px;
            text-align: center;
        }

        .label-col {
            width: 40%;
        }

        .value-col {
            width: calc(60% - 30px);
        }

        /* Double borders for top and bottom rows */
        .double-top {
            border-top: 3px double black !important;
        }

        .double-bottom {
            border-bottom: 3px double black !important;
        }

        .sub-label-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .sub-label-list li {
            margin: 2px 0;
        }

        /* ── SIGNATURE ── */
        .signature-section {
            margin-top: 30px;
            width: 100%;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sign-table td {
            width: 50%;
            vertical-align: top;
        }

        .sign-indent {
            padding-left: 20%;
        }

        .sign-space {
            height: 60px;
        }

        /* ── PAGE 2 (GRID) ── */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }

        .grid-table td {
            border: 1px solid black;
            padding: 8px;
            vertical-align: top;
            width: 50%;
        }

        .grid-block {
            margin-bottom: 10px;
        }

        .grid-label {
            margin-bottom: 5px;
        }

        .grid-placeholder {
            margin-top: 40px;
            text-align: center;
        }

        .footer-perhatian {
            margin-top: 20px;
            font-size: 10pt;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <?php foreach ($members as $idx => $member): ?>
        <!-- PAGE 1: SPD MAIN -->
        <div class="document-page <?= ($showBackPage || $idx < count($members) - 1) ? 'page-break' : '' ?>">

            <div class="spd-title">SURAT PERJALANAN DINAS (SPD)</div>

            <table class="spd-table">
                <!-- Row 1: PPK -->
                <tr class="double-top">
                    <td class="no-col">1</td>
                    <td class="label-col">Pejabat Pembuat Komitmen</td>
                    <td class="value-col"><?= esc($ppk ? $ppk->employee_name : '___________________________') ?></td>
                </tr>

                <!-- Row 2: Member -->
                <tr>
                    <td class="no-col">2</td>
                    <td class="label-col">Nama Pegawai yang melaksanakan perjalanan dinas</td>
                    <td class="value-col">
                        <span><?= esc($member->employee_name) ?></span><br>
                        NIP. <?= esc($member->employee_nip ?: '-') ?>
                    </td>
                </tr>

                <!-- Row 3: Pangkat/Jabatan/Tingkat -->
                <tr>
                    <td class="no-col">3</td>
                    <td class="label-col">
                        a. Pangkat dan Golongan<br>
                        b. Jabatan/Instansi<br>
                        c. Tingkat Biaya Perjalanan Dinas
                    </td>
                    <td class="value-col">
                        <?= trim(($member->nama_golongan ?? '') . ' / ' . ($member->kode_golongan ?? ''), ' /') ?: '-' ?><br>
                        <?= esc($member->employee_jabatan ?? '-') ?><br>
                        <?= $member->tingkatBiaya ?? '-' ?>
                    </td>
                </tr>

                <!-- Row 4: Maksud -->
                <tr>
                    <td class="no-col">4</td>
                    <td class="label-col">Maksud Perjalanan Dinas</td>
                    <td class="value-col text-justify">
                        <?= !empty($travelRequest->perihal) ? 'Mengikuti kegiatan ' . esc($travelRequest->perihal) : '-' ?>
                    </td>
                </tr>

                <!-- Row 5: Alat Angkutan -->
                <tr>
                    <td class="no-col">5</td>
                    <td class="label-col">Alat angkutan yang dipergunakan</td>
                    <td class="value-col"><?= $transportLabel ?></td>
                </tr>

                <!-- Row 6: Tempat -->
                <tr>
                    <td class="no-col">6</td>
                    <td class="label-col">
                        a. Tempat Berangkat<br>
                        b. Tempat Tujuan
                    </td>
                    <td class="value-col">
                        <?= ucwords(strtolower((string) esc($travelRequest->departure_place ?: 'Palembang'))) ?><br>
                        <?= ucwords(strtolower((string) esc($travelRequest->destination_city ? $travelRequest->destination_city . ', ' . $travelRequest->destination_province : $travelRequest->destination_province))) ?>
                    </td>
                </tr>

                <!-- Row 7: Waktu -->
                <tr>
                    <td class="no-col">7</td>
                    <td class="label-col">
                        a. Lamanya Perjalanan Dinas<br>
                        b. Tanggal Berangkat<br>
                        c. Tanggal harus kembali/tiba ditempat baru *)
                    </td>
                    <td class="value-col">
                        <?= ($travelRequest->duration_days ?? '-') ?> Hari<br>
                        <?= $tglBerangkat ?><br>
                        <?= $tglKembali ?>
                    </td>
                </tr>

                <!-- Row 8: Pengikut -->
                <tr>
                    <td class="no-col">8</td>
                    <td class="label-col">
                        Pengikut : Nama<br>
                        1.<br>
                        2.
                    </td>
                    <td class="value-col"><br>-<br></td>
                </tr>

                <!-- Row 9: Anggaran -->
                <tr>
                    <td class="no-col">9</td>
                    <td class="label-col">
                        Pembebanan Anggaran<br>
                        a. Instansi<br>
                        b. Akun
                    </td>
                    <td class="value-col">
                        <br>
                        <?= esc($travelRequest->budget_burden_by ?: 'Politeknik Negeri Sriwijaya') ?><br>
                        <?= esc($travelRequest->mak ?: '-') ?>
                    </td>
                </tr>

                <!-- Row 10: Keterangan -->
                <tr class="double-bottom">
                    <td class="no-col">10</td>
                    <td class="label-col">Keterangan lain-lain</td>
                    <td class="value-col">-</td>
                </tr>
            </table>

            <!-- SIGNATURE PAGE 1 -->
            <div class="signature-section">
                <table class="sign-table">
                    <tr>
                        <td>Tembusan disampaikan kepada :</td>
                        <td class="sign-indent">
                            DIKELUARKAN DI : <?= strtoupper((string) esc($travelRequest->departure_place ?: 'Palembang')) ?><br>
                            PADA TANGGAL : <?= strtoupper((string) $tglSurat) ?><br>
                            <br>
                            Pejabat Pembuat Komitmen,<br>
                            <div class="sign-space"></div>
                            <span class="text-bold"><?= esc($ppk ? $ppk->employee_name : '___________________________') ?></span><br>
                            NIP. <?= esc($ppk ? ($ppk->nip ?: '-') : '___________________________') ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- PAGE 2: VERIFICATION GRID (Only once at the end) -->
    <?php if ($showBackPage): ?>
        <div class="document-page">
            <table class="grid-table">
                <!-- Block I -->
                <tr>
                    <td></td>
                    <td>
                        <div class="grid-block">
                            Berangkat dari : <br>
                            Pada tanggal : <br>
                            Tujuan ke : <br>
                            <div class="grid-placeholder">(................................................)</div>
                        </div>
                    </td>
                </tr>

                <!-- Block II, III, IV... -->
                <?php
                $romans = ['II', 'III', 'IV'];
                foreach ($romans as $rom):
                ?>
                    <tr>
                        <td>
                            <div class="grid-block">
                                <?= $rom ?>. Tiba di : <br>
                                Pada tanggal : <br>
                                <br>
                                Kepala <br>
                                <div class="grid-placeholder">(................................................)<br>NIP. .........................................</div>
                            </div>
                        </td>
                        <td>
                            <div class="grid-block">
                                Berangkat dari : <br>
                                Tujuan ke : <br>
                                Pada tanggal : <br>
                                Kepala <br>
                                <div class="grid-placeholder">(................................................)<br>NIP. .........................................</div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Block Final (V) -->
                <tr>
                    <td>
                        <div class="grid-block">
                            V. Tiba di tempat kedudukan : <br>
                            Pada tanggal : <br>
                            <br>
                            <span class="text-bold">PEJABAT PEMBUAT KOMITMEN</span>
                            <div class="grid-placeholder">(................................................)<br>NIP. .........................................</div>
                        </div>
                    </td>
                    <td>
                        <div class="text-justify" style="font-size: 9pt;">
                            Telah diperiksa dengan keterangan bahwa perjalanan tersebut atas perintahnya dan semata-mata untuk kepentingan jabatan dalam waktu yang sesingkat-singkatnya.
                        </div>
                        <br>
                        <span class="text-bold">PEJABAT PEMBUAT KOMITMEN</span>
                        <div class="grid-placeholder">(................................................)<br>NIP. .........................................</div>
                    </td>
                </tr>

                <!-- Catatan -->
                <tr>
                    <td colspan="2">
                        V. CATATAN LAIN-LAIN
                    </td>
                </tr>
            </table>

            <!-- PERHATIAN -->
            <div class="footer-perhatian">
                <span class="text-bold">VI. PERHATIAN</span><br>
                <div class="text-justify">
                    PPK yang menerbitkan SPD, pegawai yang melaksanakan perjalanan dinas, para pejabat yang mengesahkan tanggal keberangkatan / tiba serta Bendahara pengeluaran bertanggung jawab berdasarkan peraturan-peraturan keuangan apabila Negara menderita rugi akibat kesalahan, kelalaian, dan kealfaannya.
                </div>
            </div>
        </div>
    <?php endif; ?>

</body>

</html>