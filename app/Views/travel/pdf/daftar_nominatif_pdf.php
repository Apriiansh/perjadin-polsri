<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 1.5cm 1cm;
            size: a4 landscape;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 8pt;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .underline {
            text-decoration: underline;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .italic {
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 2px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            vertical-align: middle;
        }

        /* Percentages for landscape (must sum to 100%) */
        .col-no {
            width: 2%;
        }

        .col-nama {
            width: 11%;
        }

        .col-nik {
            width: 12%;
        }

        .col-gol {
            width: 5%;
        }

        .col-tujuan {
            width: 5%;
        }

        .col-tgl {
            width: 6%;
        }

        .col-lama {
            width: 4%;
        }

        .col-biaya {
            width: 6.5%;
        }

        /* shared by 6 sub-columns */
        .col-jumlah {
            width: 7%;
        }

        .col-rek {
            width: 8%;
        }

        .col-ttd {
            width: 4%;
        }

        .no-border table,
        .no-border td,
        .no-border th {
            border: none !important;
        }

        .header-labels {
            font-size: 11pt;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header-labels text-center">
        <div class="bold uppercase">DAFTAR NOMINATIF PERJALANAN DINAS</div>
        <div class="bold uppercase">BIAYA PERJALANAN DINAS UANG HARIAN DAN TRANSPORT LOKAL</div>
        <div class="bold">Mata Anggaran Kegiatan (MAK) : <?= esc($travelRequest->mak ?: '-') ?></div>
    </div>

    <table>
        <thead>
            <tr class="text-center bold">
                <th class="col-no" rowspan="2">No.</th>
                <th class="col-nama" rowspan="2">Nama / ST</th>
                <th class="col-nik" rowspan="2">NIK / NIP</th>
                <th class="col-gol" rowspan="2">Pangkat / Gol</th>
                <th class="col-tujuan" rowspan="2">Tujuan</th>
                <th class="col-tgl" rowspan="2">Tanggal Berangkat - Kembali</th>
                <th class="col-lama" rowspan="2">Lama</th>
                <th colspan="7">Biaya Perjalanan Dinas</th>
                <th class="col-rek" rowspan="2">Rekening</th>
                <th class="col-ttd" rowspan="2">Tanda Tangan</th>
            </tr>
            <tr class="text-center bold">
                <th class="col-biaya">Tiket</th>
                <th class="col-biaya">Darat</th>
                <th class="col-biaya">Lokal</th>
                <th class="col-biaya">Hotel</th>
                <th class="col-biaya">Harian</th>
                <th class="col-biaya">Rep</th>
                <th class="col-jumlah">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totals = ['tiket' => 0, 'darat' => 0, 'lokal' => 0, 'hotel' => 0, 'harian' => 0, 'rep' => 0, 'grand' => 0];
            foreach ($members as $idx => $member):
                $totals['tiket']  += ($member->tiket ?? 0);
                $totals['darat']  += ($member->transport_darat ?? 0);
                $totals['lokal']  += ($member->transport_lokal ?? 0);
                $totals['hotel']  += ($member->penginapan ?? 0);
                $totals['harian'] += ($member->uang_harian ?? 0);
                $totals['rep']    += ($member->uang_representasi ?? 0);
                $totals['grand']  += ($member->total_biaya ?? 0);
            ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td>
                        <span class="bold"><?= esc($member->employee_name) ?></span><br>
                        ST No. <?= esc($travelRequest->no_surat_tugas ?: '-') ?><br>
                        Tgl <?= $tglSurat ?>
                    </td>
                    <td>
                        NIK: <?= esc($member->employee_nik ?: '-') ?><br>
                        NIP: <?= esc($member->employee_nip ?: '-') ?>
                    </td>
                    <td class="text-center">
                        <?= esc($member->nama_golongan ?? '') ?><br>
                        <?= esc($member->kode_golongan ?? '') ?>
                    </td>
                    <td class="text-center"><?= esc($travelRequest->destination_province ?: '-') ?></td>
                    <td class="text-center">
                        <?= !empty($travelRequest->departure_date) ? date('d', strtotime($travelRequest->departure_date)) : '-' ?> -
                        <?= !empty($travelRequest->return_date) ? date('d/m/Y', strtotime($travelRequest->return_date)) : '-' ?>
                    </td>
                    <td class="text-center"><?= $travelRequest->duration_days ?> Hr</td>
                    <td class="text-right"><?= number_format($member->tiket ?? 0, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($member->transport_darat ?? 0, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($member->transport_lokal ?? 0, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($member->penginapan ?? 0, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($member->uang_harian ?? 0, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($member->uang_representasi ?? 0, 0, ',', '.') ?></td>
                    <td class="text-right bold"><?= number_format($member->total_biaya ?? 0, 0, ',', '.') ?></td>
                    <td class="text-center" style="font-size: 7.5pt;"><?= esc($member->rekening_bank ?: '-') ?></td>
                    <td>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
            <tr class="bold">
                <td colspan="7" class="text-center">J U M L A H</td>
                <td class="text-right"><?= number_format($totals['tiket'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($totals['darat'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($totals['lokal'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($totals['hotel'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($totals['harian'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($totals['rep'], 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($totals['grand'], 0, ',', '.') ?></td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr class="bold">
                <td colspan="7" class="text-center">TERBILANG</td>
                <td colspan="9" class="italic" style="font-size: 7.5pt;">" <?= ucfirst(trim($terbilangGrand)) ?> Rupiah "</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 30px; width: 100%;">
        <div style="float: left; width: 40%;">
            Mengetahui,<br>
            Yang Membayar,<br><br><br><br>
            <span class="bold underline"><?= esc($bendahara ? $bendahara->employee_name : '________________________') ?></span><br>
            NIP. <?= esc($bendahara ? ($bendahara->nip ?: '-') : '________________________') ?>
        </div>
        <div style="float: right; width: 35%; text-align: left;">
            Palembang, <?= date('j F Y') ?><br>
            Yang Menerima,<br><br><br><br>
            <span class="bold">________________________</span><br>
            NIP.
        </div>
        <div style="clear: both;"></div>
    </div>
</body>

</html>