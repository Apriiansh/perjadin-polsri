<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('headStyles') ?>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.default.css" rel="stylesheet">
<link href="<?= base_url('assets/css/tom-select-custom.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/tom-select-multiple.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-1 text-sm text-slate-500">Ubah data pengajuan perjalanan dinas.</p>
    </div>
    <a href="<?= base_url('travel') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
</div>

<div class="card max-w-5xl mx-auto">
    <form action="<?= base_url('travel/' . $travelRequest->id . '/update') ?>" method="post" class="space-y-8" id="travelForm" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Section 1: Data Dasar -->
        <div>
            <h3 class="font-bold text-lg mb-4 text-slate-800 border-b border-slate-200 pb-2">1. Data Dasar</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="form-label mb-2 block">Nomor Surat Tugas <span class="text-red-500">*</span></label>
                    <input type="text" name="no_surat_tugas" class="input-control" value="<?= esc($travelRequest->no_surat_tugas) ?>" placeholder="Nomor Surat Tugas" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Surat Tugas <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_surat_tugas" class="input-control" value="<?= esc($travelRequest->tgl_surat_tugas) ?>" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label mb-2 block">Tempat Berangkat</label>
                    <input type="text" name="departure_place" class="input-control" value="<?= esc($travelRequest->departure_place ?? '') ?>" placeholder="Contoh: Palembang">
                </div>
                <div>
                    <label class="form-label mb-2 block" for="destination_province">Provinsi Tujuan <span class="text-red-500">*</span></label>
                    <select name="destination_province" id="province" class="input-control" data-old-value="<?= esc($travelRequest->destination_province) ?>" required>
                        <option value="">Sedang memuat data provinsi...</option>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-2 block" for="destination_city">Kota Tujuan <span class="text-red-500">*</span></label>
                    <select name="destination_city" id="city" class="input-control" data-old-value="<?= esc($travelRequest->destination_city) ?>" required>
                        <option value="">Pilih provinsi terlebih dahulu</option>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Berangkat <span class="text-red-500">*</span></label>
                    <input type="date" name="departure_date" id="departure_date" class="input-control" value="<?= esc($travelRequest->departure_date ?? '') ?>" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Kembali <span class="text-red-500">*</span></label>
                    <input type="date" name="return_date" id="return_date" class="input-control" value="<?= esc($travelRequest->return_date ?? '') ?>" required>
                    <p class="mt-1 text-xs text-slate-400" id="duration-info"></p>
                </div>
            </div>
        </div>

        <!-- Section 2: Surat Rujukan & Lampiran -->
        <div>
            <h3 class="font-bold text-lg mb-4 text-slate-800 border-b border-slate-200 pb-2">2. Surat Rujukan & Lampiran</h3>
            <p class="text-sm text-slate-500 mb-3">Data surat undangan atau dasar penugasan yang menjadi rujukan perjalanan dinas ini.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="form-label mb-2 block">Nomor Surat Rujukan <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_surat_rujukan" class="input-control" value="<?= esc($travelRequest->nomor_surat_rujukan ?? '') ?>" placeholder="Contoh: 123/UN9/TU/2026" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Surat Rujukan <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_surat_rujukan" class="input-control" value="<?= esc($travelRequest->tgl_surat_rujukan ?? '') ?>" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Instansi Pengirim <span class="text-red-500">*</span></label>
                    <input type="text" name="instansi_pengirim_rujukan" class="input-control" value="<?= esc($travelRequest->instansi_pengirim_rujukan ?? '') ?>" placeholder="Contoh: Kemdiktisaintek" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tahun Anggaran <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun_anggaran" class="input-control" value="<?= esc($travelRequest->tahun_anggaran ?? '') ?>" placeholder="<?= date('Y') ?>" min="2020" max="2099" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label mb-2 block">Perihal Surat Rujukan <span class="text-red-500">*</span></label>
                    <textarea name="perihal_surat_rujukan" rows="2" class="input-control" placeholder="Contoh: Undangan Mengikuti Kegiatan RESD Phase 2" required><?= esc($travelRequest->perihal_surat_rujukan ?? '') ?></textarea>
                </div>
                <div>
                    <label class="form-label mb-2 block">Lokasi / Venue <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="lokasi" class="input-control" value="<?= esc($travelRequest->lokasi ?? '') ?>" placeholder="Contoh: Hotel Grand Mercure, Jl. Sudirman No. 10">
                </div>

                <div>
                    <label class="form-label mb-2 block">Beban Anggaran <span class="text-red-500">*</span></label>
                    <input type="text" name="budget_burden_by" class="input-control" value="<?= esc($travelRequest->budget_burden_by ?? '') ?>" placeholder="Contoh: DIPA Polsri 2026" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label mb-2 block">Lampiran Surat Tugas / SK <span class="text-red-500">*</span></label>
                    <?php if (!empty($travelRequest->lampiran_original_name)): ?>
                        <div class="mb-2 flex items-center gap-2 text-sm text-slate-600 bg-slate-50 px-3 py-2 rounded border border-slate-200">
                            <i data-lucide="paperclip" class="w-4 h-4 text-slate-400"></i>
                            <span><?= esc($travelRequest->lampiran_original_name) ?></span>
                            <a href="<?= base_url('travel/' . $travelRequest->id . '/lampiran') ?>" class="text-primary-600 hover:underline text-xs ml-auto">Unduh</a>
                        </div>
                        <p class="text-xs text-slate-400 mb-1">Upload file baru untuk mengganti lampiran yang sudah ada.</p>
                    <?php endif; ?>
                    <input type="file" name="lampiran" class="input-control file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" <?= empty($travelRequest->lampiran_original_name) ? 'required' : '' ?>>
                    <p class="mt-1 text-xs text-slate-400">Format: PDF, DOC, DOCX, JPG, PNG. Maks 5MB.</p>
                </div>
            </div>
        </div>

        <!-- Section 3: Anggota Perjalanan -->
        <?php
        $selectedMembers = array_column($travelMembers, 'employee_id');
        ?>
        <div>
            <h3 class="font-bold text-lg mb-4 text-slate-800 border-b border-slate-200 pb-2">3. Anggota Perjalanan</h3>
            <?php
            $uniqueGolongan = [];
            $uniqueJurusan = [];
            foreach ($employees as $emp) {
                $gol = $emp['pangkat_golongan'] ?? '';
                $jur = $emp['nama_jurusan'] ?? '';
                if ($gol && !in_array($gol, $uniqueGolongan)) $uniqueGolongan[] = $gol;
                if ($jur && !in_array($jur, $uniqueJurusan)) $uniqueJurusan[] = $jur;
            }
            sort($uniqueGolongan);
            sort($uniqueJurusan);
            ?>

            <div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <div class="flex-1">
                        <label class="form-label text-xs mb-1 block">Filter Golongan</label>
                        <select id="filter-golongan" class="input-control text-sm py-1.5 bg-white">
                            <option value="">Semua Golongan</option>
                            <?php foreach ($uniqueGolongan as $gol): ?>
                                <option value="<?= esc($gol) ?>"><?= esc($gol) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="form-label text-xs mb-1 block">Filter Jurusan</label>
                        <select id="filter-jurusan" class="input-control text-sm py-1.5 bg-white">
                            <option value="">Semua Jurusan</option>
                            <?php foreach ($uniqueJurusan as $jur): ?>
                                <option value="<?= esc($jur) ?>"><?= esc($jur) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label mb-2 block">Cari & Pilih Pegawai <span class="text-red-500">*</span></label>
                    <select name="members[]" id="members" multiple class="w-full hidden" placeholder="Ketik nama atau NIP pegawai..." required>
                        <?php foreach ($employees as $emp) : ?>
                            <?php if (in_array($emp['id'], $selectedMembers)) : ?>
                                <option value="<?= $emp['id'] ?>"
                                    data-nip="<?= esc($emp['nip']) ?>"
                                    data-name="<?= esc($emp['name']) ?>"
                                    data-golongan="<?= esc($emp['pangkat_golongan'] ?? '-') ?>"
                                    data-jurusan="<?= esc($emp['nama_jurusan'] ?? '-') ?>"
                                    selected>
                                    <?= esc($emp['nip']) ?> - <?= esc($emp['name']) ?> - <?= esc($emp['pangkat_golongan'] ?? '-') ?> - <?= esc($emp['nama_jurusan'] ?? '-') ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Gunakan filter di atas untuk menyaring daftar. Pegawai yang terpilih akan tampil di bawah.</p>
                </div>
            </div>

            <!-- Selected Members Container -->
            <div class="mb-6">
                <h4 class="font-bold text-sm text-slate-700 mb-3 border-b pb-2">Pegawai yang Terpilih (<span id="selected-count">0</span>)</h4>
                <div id="selected-members-list" class="flex flex-col gap-2">
                    <div id="empty-members-msg" class="text-sm italic text-slate-400 p-4 border border-dashed rounded text-center">Belum ada pegawai yang dipilih.</div>
                </div>
            </div>

            <!-- Dynamic Tariff Warning Container -->
            <div id="tariff-warning-container" class="hidden mb-6 items-start gap-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" style="display: none;">
                <i data-lucide="triangle-alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500"></i>
                <div class="flex-1">
                    <p class="font-semibold mb-1">Peringatan: Beberapa tarif tujuan tidak ditemukan</p>
                    <ul id="tariff-warning-list" class="list-disc list-inside text-xs space-y-1"></ul>
                    <p class="mt-2 text-xs opacity-80">Anggota di atas tetap akan disimpan, namun komponen biaya otomatis (Uang Harian, Penginapan, Representasi) akan diset Rp 0.</p>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-200 flex justify-end gap-3">
            <a href="<?= base_url('travel') ?>" class="btn-danger">Batal</a>
            <button type="submit" name="action" value="draft" class="btn-primary">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
<script src="<?= base_url('assets/js/wilayah.js') ?>"></script>
<?php
$golData = [];
foreach ($travelMembers as $tm) {
    $golData[$tm->employee_id] = [
        'kode_golongan' => $tm->kode_golongan ?? '',
        'nama_golongan' => $tm->nama_golongan ?? '',
    ];
}
?>
<script>
    window.existingMemberGolongan = <?= json_encode($golData, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="<?= base_url('assets/js/travel-members-select.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tglMulai = document.getElementById('departure_date');
        const tglSelesai = document.getElementById('return_date');
        const durationInfo = document.getElementById('duration-info');

        function calcDuration() {
            if (tglMulai.value && tglSelesai.value) {
                const start = new Date(tglMulai.value);
                const end = new Date(tglSelesai.value);
                const days = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
                durationInfo.textContent = days > 0 ? 'Durasi: ' + days + ' hari' : 'Tanggal selesai harus >= tanggal mulai';
            } else {
                durationInfo.textContent = '';
            }
        }

        tglMulai.addEventListener('change', calcDuration);
        tglSelesai.addEventListener('change', calcDuration);
        calcDuration();
    });
</script>

<?= $this->endSection() ?>