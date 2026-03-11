<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card max-w-lg mx-auto">
    <h2 class="text-xl font-bold">Tambah Data Tarif</h2>
    <!-- Form -->
    <form action="<?= base_url('admin/tariffs/store') ?>" method="post" class="space-y-5">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="form-label" for="province">Provinsi <span class="text-red-500">*</span></label>
                <select id="province" name="province" class="input-control" data-old-value="<?= old('province') ?>" required>
                    <option value="">Sedang memuat data provinsi...</option>
                </select>
            </div>

            <div>
                <label class="form-label" for="city">Kota/Kabupaten <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                <select id="city" name="city" class="input-control" data-old-value="<?= old('city') ?>">
                    <option value="">Pilih provinsi terlebih dahulu</option>
                </select>
            </div>
        </div>

        <div>
            <label class="form-label" for="tingkat_biaya">Tingkat Biaya <span class="text-red-500">*</span></label>
            <select id="tingkat_biaya" name="tingkat_biaya" class="input-control" required>
                <option value="">Pilih Tingkat Biaya</option>
                <option value="A" <?= old('tingkat_biaya') === 'A' ? 'selected' : '' ?>>Tingkat A</option>
                <option value="B" <?= old('tingkat_biaya') === 'B' ? 'selected' : '' ?>>Tingkat B</option>
                <option value="C" <?= old('tingkat_biaya') === 'C' ? 'selected' : '' ?>>Tingkat C</option>
                <option value="D" <?= old('tingkat_biaya') === 'D' ? 'selected' : '' ?>>Tingkat D</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Pilih grading/tingkatan biaya perjalanan.</p>
        </div>



        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="form-label" for="uang_harian">Uang Harian <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-slate-500 sm:text-sm">Rp</span>
                    </div>
                    <input type="number" id="uang_harian" name="uang_harian" value="<?= old('uang_harian') ?>" class="input-control pl-10" placeholder="0" required>
                </div>
            </div>

            <div>
                <label class="form-label" for="uang_representasi">Uang Representasi <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-slate-500 sm:text-sm">Rp</span>
                    </div>
                    <input type="number" id="uang_representasi" name="uang_representasi" value="<?= old('uang_representasi') ?>" class="input-control pl-10" placeholder="0" required>
                </div>
            </div>

            <div>
                <label class="form-label" for="jenis_penginapan">Jenis Penginapan <span class="text-red-500">*</span></label>
                <input type="text" id="jenis_penginapan" name="jenis_penginapan" value="<?= old('jenis_penginapan', 'Standar Hotel') ?>" class="input-control" placeholder="Contoh: Hotel Bintang 3 / Villa" required>
                <p class="mt-1 text-xs text-slate-500">Tentukan jenis akomodasi (contoh: Standar Hotel, Hotel Bintang 4, Villa, dll).</p>
            </div>

            <div>
                <label class="form-label" for="penginapan">Biaya Penginapan <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-slate-500 sm:text-sm">Rp</span>
                    </div>
                    <input type="number" id="penginapan" name="penginapan" value="<?= old('penginapan') ?>" class="input-control pl-10" placeholder="0" required>
                </div>
            </div>

        </div>

        <div>
            <label class="form-label" for="tahun_berlaku">Tahun Berlaku <span class="text-red-500">*</span></label>
            <input type="number" id="tahun_berlaku" name="tahun_berlaku" value="<?= old('tahun_berlaku', date('Y')) ?>" class="input-control" placeholder="YYYY" required>
        </div>

        <div class="flex items-center justify-between rounded-lg border border-surface-200 p-4">
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-slate-900">Status Aktif</span>
                <span class="text-xs text-slate-500">Aktifkan data tarif ini untuk dapat digunakan pada transaksi</span>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
                <!-- Checkbox -->
                <input type="checkbox" name="is_active" value="1" class="peer sr-only" <?= old('is_active', '1') == '1' ? 'checked' : '' ?>>

                <!-- Toggle background -->
                <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-primary-300"></div>
            </label>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="<?= base_url('admin/tariffs') ?>" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan Tarif
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/js/wilayah.js') ?>" defer></script>
<?= $this->endSection() ?>