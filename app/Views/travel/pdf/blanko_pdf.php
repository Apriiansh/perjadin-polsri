<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Blanko Kosong SPD</title>
    <style>
        @page {
            margin: 1.5cm 2.0cm 1.5cm 2.5cm; /* T R B L */
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-underline { text-decoration: underline; }
        .text-justify { text-align: justify; }

        /* ── GRID TABLE ── */
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

        .grid-placeholder {
            margin-top: 40px;
            text-align: center;
        }

        .footer-perhatian {
            margin-top: 20px;
            font-size: 10pt;
        }
    </style>
</head>
<body>

<div class="document-page">
    <table class="grid-table">
        <!-- Block I -->
        <tr>
            <td></td>
            <td>
                <div class="grid-block">
                    Berangkat dari : <br>
                    Pada tanggal   : <br>
                    Tujuan ke      : <br>
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
                    Pada tanggal  : <br>
                    <br>
                    Kepala <br>
                    <div class="grid-placeholder">(................................................)<br>NIP. .........................................</div>
                </div>
            </td>
            <td>
                <div class="grid-block">
                    Berangkat dari : <br>
                    Tujuan ke      : <br>
                    Pada tanggal   : <br>
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
                    Pada tanggal   : <br>
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

</body>
</html>
