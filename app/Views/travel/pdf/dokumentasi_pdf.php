<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 1.5cm; size: a4 portrait; }
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .uppercase { text-transform: uppercase; }
        
        .header { margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 14pt; margin-bottom: 20px; }
        
        .image-grid { width: 100%; border-collapse: separate; border-spacing: 20px; }
        .image-item { width: 45%; vertical-align: top; text-align: center; border: 1px solid #ddd; padding: 10px; background-color: #fafafa; }
        .image-container { width: 100%; height: 180px; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
        .image-container img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .image-caption { font-size: 9pt; color: #555; height: 35px; overflow: hidden; line-height: 1.2; }
    </style>
</head>
<body>
    <div class="header text-center">
        <div class="bold" style="font-size: 12pt;">POLITEKNIK NEGERI SRIWIJAYA</div>
        <div class="bold uppercase">DOKUMENTASI PERJALANAN DINAS</div>
        <div style="font-size: 10pt;">No. Surat Tugas: <?= esc($travelRequest->no_surat_tugas ?: '-') ?></div>
    </div>

    <h3 class="text-center bold underline uppercase title">Lampiran Dokumentasi</h3>

    <?php if (empty($images)): ?>
        <div class="text-center italic" style="margin-top: 50px; color: #888;">
            (Tidak ada dokumentasi foto yang diunggah)
        </div>
    <?php else: ?>
        <table class="image-grid">
            <?php 
            $chunks = array_chunk($images, 2);
            foreach ($chunks as $row): ?>
                <tr>
                    <?php foreach ($row as $img): ?>
                        <td class="image-item">
                            <div class="image-container">
                                <img src="<?= $img['base64'] ?>" alt="Dokumentasi">
                            </div>
                            <div class="image-caption">
                                <span class="bold"><?= esc($img['title']) ?></span>
                            </div>
                        </td>
                    <?php endforeach; ?>
                    <?php if (count($row) == 1): ?>
                        <td style="width: 45%; border: none;"></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div style="margin-top: 50px; padding-left: 60%;">
        <div class="text-center">
            Palembang, <?= date('j F Y') ?><br>
            Mengetahui,<br>
            Pejabat Pembuat Komitmen<br><br><br><br><br>
            <span class="bold underline"><?= esc($ppk ? $ppk->employee_name : '___________________________') ?></span><br>
            NIP. <?= esc($ppk ? ($ppk->nip ?: '-') : '___________________________') ?>
        </div>
    </div>
</body>
</html>
