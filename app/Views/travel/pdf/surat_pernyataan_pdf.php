<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan - <?= esc($travelRequest->no_surat_tugas ?: 'Dokumen') ?></title>
    <style>
        @page {
            margin: 1.5cm 2.0cm 1.5cm 2.5cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        
        .kop-table {
            width: 100%;
            border-bottom: 2px solid black;
            margin-bottom: 20px;
        }
        .document-page {
            position: relative;
        }
        .page-break {
            page-break-after: always;
        }
        .signature-block {
            width: 100%;
            margin-top: 50px;
        }
        .signature-block td {
            vertical-align: top;
        }
    </style>
</head>
<body>

<?php foreach ($members as $idx => $member): ?>
<div class="document-page <?= ($idx < count($members) - 1) ? 'page-break' : '' ?>">
    <!-- HEADER -->
    <table class="kop-table">
        <tr>
            <td style="width: 80px; text-align: center; padding-bottom: 5px;">
                <?php if (file_exists(FCPATH . 'img/logo-polsri-bnw.jpg')): ?>
                    <img src="<?= 'data:image/jpeg;base64,' . base64_encode(file_get_contents(FCPATH . 'img/logo-polsri-bnw.jpg')) ?>" style="width: 70px;">
                <?php endif; ?>
            </td>
            <td style="text-align: center; padding-right: 40px; padding-bottom: 5px;">
                <div style="font-size: 11pt;">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</div>
                <div style="font-size: 13pt; font-weight: bold;">POLITEKNIK NEGERI SRIWIJAYA</div>
                <div style="font-size: 9pt;">Jalan Srijaya Negara Bukit Besar – Palembang 30139</div>
                <div style="font-size: 9pt;">Telp. 0711-353414 Fax. 0711-355918</div>
                <div style="font-size: 9pt; color: blue; text-decoration: underline;">Laman : http://polsri.ac.id</div>
            </td>
        </tr>
    </table>

    <div class="text-center bold underline" style="font-size: 14pt; margin-top: 20px; margin-bottom: 30px;">SURAT PERNYATAAN</div>

    <p>Yang bertanda tangan di bawah ini :</p>

    <table style="margin-left: 20px; margin-bottom: 20px;">
        <tr>
            <td style="width: 120px;">Nama</td>
            <td style="width: 10px;">:</td>
            <td class="bold"><?= esc($member->employee_name) ?></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td class="bold"><?= esc($member->employee_nip ?: '-') ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td class="bold"><?= esc($member->employee_jabatan ?? '-') ?></td>
        </tr>
    </table>

    <p class="text-justify">
        Berdasarkan Surat Tugas tanggal <?= $tglSuratTugas ?> Nomor: <?= esc($travelRequest->no_surat_tugas ?: '__________________') ?> dengan ini kami menyatakan dengan sesungguhnya bahwa :
    </p>

    <table style="margin-top: 15px; margin-left: 10px;">
        <tr>
            <td style="width: 30px; vertical-align: top;">1.</td>
            <td class="text-justify">
                Bukti-bukti (Tiket / bukti transportasi, Boarding Pass, Kwitansi, Hotel bill / tagihan hotel dan sebagainya) yang dilampirkan dalam rangka melakukan perjalanan dinas adalah bukti-bukti asli dan benar yang dikeluarkan oleh perusahaan / instansi yang berwenang untuk menerbitkan bukti-bukti tersebut.
            </td>
        </tr>
        <tr style="height: 15px;"><td></td><td></td></tr>
        <tr>
            <td style="width: 30px; vertical-align: top;">2.</td>
            <td class="text-justify">
                Apabila dikemudian hari terdapat kesalahan atau temuan dari aparat pengawasan fungsional, kami bersedia untuk mempertanggungjawabkannya.
            </td>
        </tr>
    </table>

    <p style="margin-top: 20px;" class="text-justify">
        Demikian pernyataan ini kami buat dengan sebenarnya, untuk dipertanggungjawabkan sebagaimana mestinya.
    </p>

    <table class="signature-block">
        <tr>
            <td style="width: 50%;">
                Mengetahui/Menyetujui<br>
                an. Kuasa Pengguna Anggaran<br>
                Pejabat Pembuat Komitmen<br><br><br><br><br>
                <span class="bold underline"><?= esc($ppk ? $ppk->employee_name : '___________________________') ?></span><br>
                NIP. <?= esc($ppk ? ($ppk->nip ?: '-') : '___________________________') ?>
            </td>
            <td style="padding-left: 50px;">
                <?= esc($tempatTerbit) ?>, <?= $tglTandaTangan ?><br>
                Yang melakukan Perjalanan Dinas,<br><br><br><br><br><br>
                <span class="bold underline"><?= esc($member->employee_name) ?></span><br>
                NIP. <?= esc($member->employee_nip ?: '-') ?>
            </td>
        </tr>
    </table>
</div>
<?php endforeach; ?>

</body>
</html>
