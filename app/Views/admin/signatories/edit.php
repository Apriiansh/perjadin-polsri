<?= $this->extend('layouts/main') ?>

<?= $this->section('headStyles') ?>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.default.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/tom-select-custom.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card max-w-lg mx-auto">
    <h2 class="text-xl font-bold">Ubah Data Penandatangan</h2>

    <!-- Form -->
    <form action="<?= base_url('admin/signatories/' . $signatory->id . '/update') ?>" method="post" class="space-y-5">
        <?= csrf_field() ?>

        <div>
            <label class="form-label" for="employee_id">Pilih Pegawai <span class="text-red-500">*</span></label>
            <select id="employee_id" name="employee_id" class="input-control" required>
                <option value="">-- Pilih Pegawai --</option>
                <?php foreach ($employees as $emp) : ?>
                    <option value="<?= $emp['id'] ?>" <?= old('employee_id', $signatory->employee_id) == $emp['id'] ? 'selected' : '' ?>>
                        <?= esc($emp['name']) ?> (NIP: <?= esc($emp['nip']) ?>)
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div>
            <label class="form-label" for="jabatan">Jabatan Penandatangan <span class="text-red-500">*</span></label>
            <select id="jabatan" name="jabatan" class="input-control" required>
                <option value="">-- Pilih Peran/Jabatan --</option>
                <option value="Pejabat Pembuat Komitmen (PPK)" <?= old('jabatan', $signatory->jabatan) == 'Pejabat Pembuat Komitmen (PPK)' ? 'selected' : '' ?>>Pejabat Pembuat Komitmen (PPK)</option>
                <option value="Bendahara Pengeluaran" <?= old('jabatan', $signatory->jabatan) == 'Bendahara Pengeluaran' ? 'selected' : '' ?>>Bendahara Pengeluaran</option>
            </select>
        </div>

        <div class="flex items-center justify-between rounded-lg border border-surface-200 p-4">
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-slate-900">Status Aktif</span>
                <span class="text-xs text-slate-500">Nonaktifkan apabila pejabat ini sudah dipindah tugaskan / tidak menerima SPPD lagi.</span>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
                <!-- Checkbox -->
                <input type="checkbox" name="is_active" value="1" class="peer sr-only" <?= old('is_active', $signatory->is_active) == '1' ? 'checked' : '' ?>>
                <!-- Toggle background -->
                <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-secondary-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-primary-300"></div>
            </label>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="<?= base_url('admin/signatories') ?>" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
<script src="<?= base_url('assets/js/tom-select-init.js') ?>"></script>
<?= $this->endSection() ?>