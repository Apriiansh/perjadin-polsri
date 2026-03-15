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
        <p class="mt-1 text-sm text-slate-500">Buat permintaan perjalanan dinas baru untuk dosen.</p>
    </div>
    <a href="<?= base_url('travel') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
</div>

<div class="card max-w-5xl mx-auto">
    <form action="<?= base_url('travel/store') ?>" method="post" class="space-y-8" id="travelForm" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Section 1: Data Dasar -->
        <div>
            <h3 class="font-bold text-lg mb-4 text-slate-800 border-b border-slate-200 pb-2">1. Data Dasar</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="form-label mb-2 block">Nomor Surat Tugas <span class="text-red-500">*</span></label>
                    <input type="text" name="no_surat_tugas" class="input-control" placeholder="Nomor Surat Tugas" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Surat Tugas <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_surat_tugas" class="input-control" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label mb-2 block">Tempat Berangkat</label>
                    <input type="text" name="departure_place" value="Palembang" class="input-control" placeholder="Contoh: Palembang">
                </div>
                <div>
                    <label class="form-label mb-2 block" for="destination_province">Provinsi Tujuan <span class="text-red-500">*</span></label>
                    <select name="destination_province" id="province" class="input-control" required>
                        <option value="">Sedang memuat data provinsi...</option>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-2 block" for="destination_city">Kota Tujuan <span class="text-red-500">*</span></label>
                    <select name="destination_city" id="city" class="input-control" required>
                        <option value="">Pilih provinsi terlebih dahulu</option>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Berangkat <span class="text-red-500">*</span></label>
                    <input type="date" name="departure_date" id="departure_date" class="input-control" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Kembali <span class="text-red-500">*</span></label>
                    <input type="date" name="return_date" id="return_date" class="input-control" required>
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
                    <input type="text" name="nomor_surat_rujukan" class="input-control" placeholder="Contoh: 123/UN9/TU/2026" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Surat Rujukan <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_surat_rujukan" class="input-control" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Instansi Pengirim <span class="text-red-500">*</span></label>
                    <input type="text" name="instansi_pengirim_rujukan" class="input-control" placeholder="Contoh: Kemdiktisaintek" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tahun Anggaran <span class="text-red-500">*</span></label>
                    <input type="number" value="<?= date('Y') ?>" name="tahun_anggaran" class="input-control" placeholder="<?= date('Y') ?>" min="2020" max="2099" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label mb-2 block">Perihal Surat Rujukan <span class="text-red-500">*</span></label>
                    <textarea name="perihal_surat_rujukan" rows="2" class="input-control" placeholder="Contoh: Undangan Mengikuti Kegiatan RESD Phase 2" required></textarea>
                </div>
                <div>
                    <label class="form-label mb-2 block">Lokasi / Venue <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="lokasi" class="input-control" placeholder="Contoh: Hotel Grand Mercure, Jl. Sudirman No. 10">
                </div>

                <div>
                    <label class="form-label mb-2 block">Beban Anggaran <span class="text-red-500">*</span></label>
                    <input type="text" name="budget_burden_by" class="input-control" placeholder="Contoh: DIPA Polsri 2026" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label mb-2 block">Lampiran Surat Tugas / SK <span class="text-red-500">*</span></label>
                    <input type="file" name="lampiran" class="input-control file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                    <p class="mt-1 text-xs text-slate-400">Format: PDF, DOC, DOCX, JPG, PNG. Maks 5MB.</p>
                </div>
            </div>
        </div>

        <!-- Section 3: Anggota Perjalanan -->
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

        </div>

        <div class="pt-6 border-t border-slate-200 flex justify-end gap-3">
            <a href="<?= base_url('travel') ?>" class="btn-danger">Batal</a>
            <button type="submit" name="action" value="draft" class="btn-primary">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
<script src="<?= base_url('assets/js/wilayah.js') ?>"></script>
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
    });
</script>

<?= $this->endSection() ?>