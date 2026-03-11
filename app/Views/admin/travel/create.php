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
        <p class="mt-1 text-sm text-slate-500">Buat permintaan perjalanan dinas baru untuk anggota tim.</p>
    </div>

    <a href="<?= base_url('admin/travel') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
</div>

<div class="card max-w-5xl mx-auto">
    <form action="<?= base_url('admin/travel/store') ?>" method="post" class="space-y-8" id="travelForm">
        <?= csrf_field() ?>

        <!-- Section 1: Data Dasar -->
        <div>
            <h3 class="font-bold text-lg mb-4 text-slate-800 border-b border-slate-200 pb-2">1. Data Dasar</h3>
            <!-- <div class="mb-2">
                <label class="form-label mb-2 block">Pembuat Request</label>
                <input type="hidden" name="employee_id" value="1">
                <input type="text" class="input-control bg-slate-50 text-slate-500" value="Admin" readonly>
            </div> -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="form-label mb-2 block">Surat Tugas (Opsional)</label>
                    <input type="text" name="no_surat_tugas" class="input-control" placeholder="Nomor Surat Tugas">
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Surat Tugas (Opsional)</label>
                    <input type="date" name="tgl_surat_tugas" class="input-control">
                </div>
                <div>
                    <label class="form-label mb-2 block">Nomor SPPD (Opsional)</label>
                    <input type="text" name="no_sppd" class="input-control" placeholder="Nomor SPPD">
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal SPPD (Opsional)</label>
                    <input type="date" name="tgl_sppd" class="input-control">
                </div>
                <div>
                    <label class="form-label mb-2 block" for="destination_province">Provinsi Tujuan</label>
                    <select name="destination_province" id="province" class="input-control" required>
                        <option value="">Sedang memuat data provinsi...</option>
                    </select>
                </div>

                <div>
                    <label class="form-label mb-2 block" for="destination_city">Kota Tujuan <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                    <select name="destination_city" id="city" class="input-control">
                        <option value="">Pilih provinsi terlebih dahulu</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label mb-2 block">Tujuan Acara / Lokasi</label>
                    <input type="text" name="destination" class="input-control" placeholder="Contoh: Sehubung dengan surat ini ..." required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label mb-2 block">Maksud Perjalanan Secara Singkat</label>
                    <textarea name="purpose" rows="3" class="input-control" placeholder="Contoh: Mengikuti kegiatan ..." required></textarea>
                </div>
            </div>

        </div>

        <!-- Section 2: Jadwal & Kendaraan -->
        <div>
            <h3 class="font-bold text-lg mb-4 text-slate-800 border-b border-slate-200 pb-2">2. Jadwal & Kendaraan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div>
                    <label class="form-label mb-2 block">MAK (Opsional)</label>
                    <input type="text" name="mak" class="input-control" placeholder="Contoh: 021.05.XXXX">
                </div>
                <div>
                    <label class="form-label mb-2 block">Jenis Transportasi</label>
                    <select name="transportation_type" class="input-control" required>
                        <option value="udara">Transportasi Udara</option>
                        <option value="darat">Transportasi Darat</option>
                        <option value="laut">Transportasi Laut</option>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tempat Berangkat</label>
                    <input type="text" name="origin" class="input-control" value="Palembang" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Berangkat</label>
                    <input type="date" name="departure_date" id="departure_date" class="input-control" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Tanggal Pulang</label>
                    <input type="date" name="return_date" id="return_date" class="input-control" required>
                </div>
                <div>
                    <label class="form-label mb-2 block">Durasi (Hari)</label>
                    <div class="relative">
                        <input type="number" name="duration_days" id="duration_days" class="input-control bg-slate-50 pr-12 text-slate-600 font-bold" readonly required placeholder="0">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-slate-500 sm:text-sm">Hari</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Anggota Tim -->
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
                        <!-- Options will be loaded via AJAX -->
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
                    <ul id="tariff-warning-list" class="list-disc list-inside text-xs space-y-1">
                        <!-- Populated by JS -->
                    </ul>
                    <p class="mt-2 text-xs opacity-80">Anggota di atas tetap akan disimpan, namun komponen biaya otomatis (Uang Harian, Penginapan, Representasi) akan diset Rp 0.</p>
                </div>
            </div>
        </div>

        <!-- Section 4: Penandatangan -->
        <div>
            <h3 class="font-bold text-lg mb-4 text-slate-800 border-b border-slate-200 pb-2">4. Pejabat Penandatangan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="form-label mb-2 block">Penjabat Pembuat Komitmen (PPK)</label>
                    <select name="signatory_ppk_id" id="signatory_ppk_id" class="w-full" placeholder="Pilih PPK..." required>
                        <option value="">Pilih PPK</option>
                        <!-- Ideally filter this by role -->
                        <?php foreach ($signatories as $sig) : ?>
                            <option value="<?= $sig->id ?>"><?= esc($sig->nip) ?> - <?= esc($sig->employee_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-2 block">Kuasa Pengguna Anggaran (KPA)</label>
                    <select name="signatory_kpa_id" id="signatory_kpa_id" class="w-full" placeholder="Pilih KPA..." required>
                        <option value="">Pilih KPA</option>
                        <!-- Ideally filter this by role -->
                        <?php foreach ($signatories as $sig) : ?>
                            <option value="<?= $sig->id ?>"><?= esc($sig->nip) ?> - <?= esc($sig->employee_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-200 flex justify-end gap-3">
            <a href="<?= base_url('admin/travel') ?>" class="btn-danger">Batal</a>
            <button type="submit" name="action" value="draft" class="btn-secondary">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan Sebagai Draft
            </button>
            <button type="submit" name="action" value="submit" class="btn-primary">
                <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                Ajukan Langsung
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
<script src="<?= base_url('assets/js/wilayah.js') ?>"></script>
<script src="<?= base_url('assets/js/travel-members-select.js') ?>"></script>
<script src="<?= base_url('assets/js/tom-select-init.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Signatories TomSelects (generic)
        initTomSelect('#signatory_ppk_id');
        initTomSelect('#signatory_kpa_id');

        // Calculate Duration
        const departureInput = document.getElementById('departure_date');
        const returnInput = document.getElementById('return_date');
        const durationInput = document.getElementById('duration_days');

        function calculateDuration() {
            if (departureInput.value && returnInput.value) {
                const depDate = new Date(departureInput.value);
                const retDate = new Date(returnInput.value);

                const diffTime = Math.abs(retDate - depDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                if (retDate >= depDate) {
                    durationInput.value = diffDays;
                } else {
                    durationInput.value = 0;
                    alert("Tanggal pulang tidak boleh sebelum tanggal berangkat!");
                    returnInput.value = '';
                }
            }
        }

        departureInput.addEventListener('change', calculateDuration);
        returnInput.addEventListener('change', calculateDuration);
    });
</script>
<?= $this->endSection() ?>