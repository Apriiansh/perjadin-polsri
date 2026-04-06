<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="px-5 py-6">
    <!-- Page header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= base_url('travel/active') ?>" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <!-- <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Dokumen Mandiri</span> -->
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
            <p class="mt-0.5 text-sm text-slate-500">Preview lembar verifikasi (Halaman Belakang) Surat Perjalanan Dinas.</p>
        </div>
        <div>
            <a href="<?= base_url('blanko-kosong/download') ?>" class="btn-primary inline-flex items-center gap-2 text-sm shadow-md shadow-primary-500/20">
                <i data-lucide="download" class="w-4 h-4"></i>
                Download PDF
            </a>
        </div>
    </div>

    <!-- Preview Card -->
    <div class="card p-0 overflow-hidden border-t-4 border-t-primary-600 shadow-lg bg-white">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 uppercase tracking-wider text-xs">
                <i data-lucide="eye" class="w-4 h-4 text-primary-600"></i>
                Tinjauan Dokumen
            </h3>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                Kertas A4 · Portrait
            </span>
        </div>

        <div class="p-8 md:p-12 bg-slate-100/50">
            <div class="mx-auto max-w-4xl border border-slate-200 p-10 bg-white shadow-2xl rounded-sm" style="font-family: 'Times New Roman', serif; color: black; line-height: 1.4; min-height: 29.7cm;">

            <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
                <!-- Block I -->
                <tr>
                    <td style="width: 50%; border: 1px solid black; padding: 12px;"></td>
                    <td style="width: 50%; border: 1px solid black; padding: 12px; vertical-align: top;">
                        <div style="margin-bottom: 20px;">
                            Berangkat dari : <br>
                            Pada tanggal : <br>
                            Tujuan ke : <br>
                            <div style="margin-top: 50px; text-align: center;">(................................................)</div>
                        </div>
                    </td>
                </tr>

                <!-- Block II, III, IV -->
                <?php foreach (['II', 'III', 'IV'] as $rom): ?>
                    <tr>
                        <td style="border: 1px solid black; padding: 12px; vertical-align: top;">
                            <div style="margin-bottom: 20px;">
                                <?= $rom ?>. Tiba di : <br>
                                Pada tanggal : <br>
                                <br>
                                Kepala <br>
                                <div style="margin-top: 50px; text-align: center;">(................................................)<br>NIP. .........................................</div>
                            </div>
                        </td>
                        <td style="border: 1px solid black; padding: 12px; vertical-align: top;">
                            <div style="margin-bottom: 20px;">
                                Berangkat dari : <br>
                                Tujuan ke : <br>
                                Pada tanggal : <br>
                                Kepala <br>
                                <div style="margin-top: 50px; text-align: center;">(................................................)<br>NIP. .........................................</div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Block V -->
                <tr>
                    <td style="border: 1px solid black; padding: 12px; vertical-align: top;">
                        <div style="margin-bottom: 20px;">
                            V. Tiba di tempat kedudukan : <br>
                            Pada tanggal : <br>
                            <br>
                            <span style="font-weight: bold;">PEJABAT PEMBUAT KOMITMEN</span>
                            <div style="margin-top: 50px; text-align: center;">(................................................)<br>NIP. .........................................</div>
                        </div>
                    </td>
                    <td style="border: 1px solid black; padding: 12px; vertical-align: top;">
                        <div style="text-align: justify; font-size: 10pt;">
                            Telah diperiksa dengan keterangan bahwa perjalanan tersebut atas perintahnya dan semata-mata untuk kepentingan jabatan dalam waktu yang sesingkat-singkatnya.
                        </div>
                        <br>
                        <span style="font-weight: bold;">PEJABAT PEMBUAT KOMITMEN</span>
                        <div style="margin-top: 50px; text-align: center;">(................................................)<br>NIP. .........................................</div>
                    </td>
                </tr>

                <!-- Catatan -->
                <tr>
                    <td colspan="2" style="border: 1px solid black; padding: 8px;">
                        V. CATATAN LAIN-LAIN
                    </td>
                </tr>
            </table>

            <div style="margin-top: 40px; font-size: 10pt;">
                <span style="font-weight: bold;">VI. PERHATIAN</span><br>
                <div style="text-align: justify;">
                    PPK yang menerbitkan SPD, pegawai yang melaksanakan perjalanan dinas, para pejabat yang mengesahkan tanggal keberangkatan / tiba serta Bendahara pengeluaran bertanggung jawab berdasarkan peraturan-peraturan keuangan apabila Negara menderita rugi akibat kesalahan, kelalaian, dan kealfaannya.
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>