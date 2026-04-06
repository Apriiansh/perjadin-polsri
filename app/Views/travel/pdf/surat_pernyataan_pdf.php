<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan</title>
    <style>
        @page {
            margin: 1.5cm 2.0cm 1.5cm 2.5cm; /* T R B L */
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ── HEADER / KOP ── */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .kop-table td {
            vertical-align: middle;
            padding-bottom: 8px;
        }

        .logo-cell {
            width: 80px;
            text-align: center;
        }

        .logo-img {
            width: 75px;
            height: 75px;
        }

        .text-cell {
            text-align: center;
            padding-right: 40px; 
        }

        .kop-header {
            font-size: 14pt;
            margin: 0;
        }

        .kop-title {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
        }

        .kop-contact {
            font-size: 10pt;
            margin: 0;
        }

        .kop-link {
            font-size: 10pt;
            color: blue;
            text-decoration: underline;
        }

        /* ── CONTENT ── */
        .title-block {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .title-text {
            font-weight: bold;
            text-decoration: underline;
            font-size: 13pt;
        }

        .section-gap {
            margin-top: 15px;
        }

        .data-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }

        .data-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .label-col {
            width: 120px;
        }

        .separator-col {
            width: 20px;
            text-align: center;
        }

        .value-col {
            font-weight: bold;
        }

        .justify {
            text-align: justify;
        }

        .list-item {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .list-no {
            display: table-cell;
            width: 30px;
            vertical-align: top;
        }

        .list-text {
            display: table-cell;
            vertical-align: top;
            text-align: justify;
        }

        /* ── SIGNATURE ── */
        .signature-block {
            margin-top: 40px;
            width: 100%;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sign-table td:first-child {
            width: 60%;
            vertical-align: top;
        }

        .sign-table td:last-child {
            width: 40%;
            vertical-align: top;
        }

        .sign-title {
            margin-bottom: 60px;
        }

        .sign-name {
            font-weight: bold;
            margin: 0;
        }

        .sign-nip {
            margin: 0;
        }

        /* ── FOOTER ── */
        #footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 50px;
            text-align: left;
        }

        .iso-img {
            height: 35px;
        }

        /* ── PAGE BREAK ── */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

<?php foreach ($members as $idx => $member): ?>
    <div class="document-page <?= ($idx < count($members) - 1) ? 'page-break' : '' ?>">
        
        <!-- HEADER -->
        <table class="kop-table">
            <tr>
                <td class="logo-cell">
                    <?php if (file_exists(FCPATH . 'img/logo-polsri-bnw.jpg')): ?>
                        <img src="<?= 'data:image/jpeg;base64,' . base64_encode(file_get_contents(FCPATH . 'img/logo-polsri-bnw.jpg')) ?>" class="logo-img">
                    <?php endif; ?>
                </td>
                <td class="text-cell">
                    <div class="kop-header">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,</div>
                    <div class="kop-header">RISET, DAN TEKNOLOGI</div>
                    <div class="kop-title">POLITEKNIK NEGERI SRIWIJAYA</div>
                    <div class="kop-contact">Jalan Srijaya Negara Bukit Besar – Palembang 30139</div>
                    <div class="kop-contact">Telp. 0711-353414 Fax. 0711-355918</div>
                    <div class="kop-link">Laman : http://polsri.ac.id</div>
                </td>
            </tr>
        </table>

        <!-- TITLE -->
        <div class="title-block">
            <span class="title-text">SURAT PERNYATAAN</span>
        </div>

        <!-- INTRO -->
        <div class="section-gap">
            Yang bertanda tangan di bawah ini :
        </div>

        <!-- DATA -->
        <table class="data-table">
            <tr>
                <td class="label-col">Nama</td>
                <td class="separator-col">:</td>
                <td class="value-col"><?= esc($member->employee_name) ?></td>
            </tr>
            <tr>
                <td class="label-col">NIP</td>
                <td class="separator-col">:</td>
                <td class="value-col"><?= esc($member->employee_nip ?: '-') ?></td>
            </tr>
            <tr>
                <td class="label-col">Jabatan</td>
                <td class="separator-col">:</td>
                <td class="value-col"><?= esc($member->employee_jabatan ?: '-') ?></td>
            </tr>
        </table>

        <!-- BODY -->
        <div class="section-gap justify">
            Berdasarkan Surat Tugas tanggal <?= $tglSuratTugas ?> 
            Nomor: <?= esc($travelRequest->no_surat_tugas ?: '__________________') ?> 
            dengan ini kami menyatakan dengan sesungguhnya bahwa :
        </div>

        <!-- LIST -->
        <div class="section-gap">
            <div class="list-item">
                <div class="list-no">1.</div>
                <div class="list-text">
                    Bukti-bukti (Tiket / bukti transportasi, Boarding Pass, Kwitansi, Hotel bill / tagihan hotel 
                    dan sebagainya) yang dilampirkan dalam rangka melakukan perjalanan dinas adalah bukti-bukti 
                    asli dan benar yang dikeluarkan oleh perusahaan / instansi yang berwenang untuk menerbitkan 
                    bukti-bukti tersebut.
                </div>
            </div>
            <div class="list-item">
                <div class="list-no">2.</div>
                <div class="list-text">
                    Apabila dikemudian hari terdapat kesalahan atau temuan dari aparat pengawasan fungsional, 
                    kami bersedia untuk mempertanggungjawabkannya.
                </div>
            </div>
        </div>

        <!-- CLOSING -->
        <div class="section-gap justify">
            Demikian pernyataan ini kami buat dengan sebenarnya, untuk dipertanggungjawabkan sebagaimana mestinya.
        </div>

        <!-- SIGNATURES -->
        <div class="signature-block">
            <table class="sign-table">
                <tr>
                    <td>
                        <div class="sign-title">
                            Mengetahui/Menyetujui<br>
                            an. Kuasa Pengguna Anggaran<br>
                            Pejabat Pembuat Komitmen
                        </div>
                        <p class="sign-name"><?= esc($ppk ? $ppk->employee_name : '___________________________') ?></p>
                        <p class="sign-nip">NIP. <?= esc($ppk ? ($ppk->nip ?: '-') : '___________________________') ?></p>
                    </td>
                    <td>
                        <div class="sign-title">
                            <br>
                            <?= esc($travelRequest->departure_place ?: 'Palembang') ?>, <?= $tglTandaTangan ?><br>
                            Yang melakukan Perjalanan Dinas,
                        </div>
                        <p class="sign-name"><?= esc($member->employee_name) ?></p>
                        <p class="sign-nip">NIP. <?= esc($member->employee_nip ?: '-') ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER (ISO LOGO) -->
        <div id="footer">
            <?php if (file_exists(FCPATH . 'img/iso.png')): ?>
                <img src="<?= 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . 'img/iso.png')) ?>" class="iso-img">
            <?php endif; ?>
        </div>

    </div>
<?php endforeach; ?>

</body>
</html>
