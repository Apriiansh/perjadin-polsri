<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex items-center gap-4">
    <a href="<?= base_url('admin/students/' . $student->id) ?>" class="btn-secondary p-2 inline-flex items-center justify-center rounded-md">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Edit Data Mahasiswa</h1>
        <p class="mt-1 text-sm text-slate-500">Sesuaikan informasi identitas mahasiswa.</p>
    </div>
</div>

<div class="max-w-2xl">
    <div class="card p-8 border-none shadow-soft bg-white rounded-md">
        <form action="<?= base_url('admin/students/' . $student->id . '/update') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="space-y-6">
                <!-- NIM -->
                <div class="space-y-2">
                    <label for="nim" class="block text-sm font-bold text-slate-700">NIM</label>
                    <input type="text" name="nim" id="nim" value="<?= old('nim', $student->nim) ?>" 
                        class="form-input rounded-md w-full border-slate-200 focus:border-primary-500 focus:ring-primary-500/20" 
                        placeholder="Contoh: 062140411234">
                    <?php if (isset(session('errors')['nim'])): ?>
                        <p class="text-xs text-rose-500 font-medium"><?= session('errors')['nim'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Nama -->
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-bold text-slate-700">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="<?= old('name', $student->name) ?>" 
                        class="form-input rounded-md w-full border-slate-200 focus:border-primary-500 focus:ring-primary-500/20" 
                        placeholder="Contoh: Ahmad Junaidi">
                    <?php if (isset(session('errors')['name'])): ?>
                        <p class="text-xs text-rose-500 font-medium"><?= session('errors')['name'] ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Jurusan -->
                    <div class="space-y-2">
                        <label for="jurusan" class="block text-sm font-bold text-slate-700">Jurusan</label>
                        <select name="jurusan" id="jurusan" class="form-input rounded-md w-full border-slate-200 focus:border-primary-500 focus:ring-primary-500/20 text-sm">
                            <option value="">Pilih Jurusan</option>
                        </select>
                        <?php if (isset(session('errors')['jurusan'])): ?>
                            <p class="text-xs text-rose-500 font-medium"><?= session('errors')['jurusan'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Prodi -->
                    <div class="space-y-2">
                        <label for="prodi" class="block text-sm font-bold text-slate-700">Program Studi</label>
                        <select name="prodi" id="prodi" class="form-input rounded-md w-full border-slate-200 focus:border-primary-500 focus:ring-primary-500/20 text-sm">
                            <option value="">Pilih Jurusan Dulu</option>
                        </select>
                        <?php if (isset(session('errors')['prodi'])): ?>
                            <p class="text-xs text-rose-500 font-medium"><?= session('errors')['prodi'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="<?= base_url('admin/students/' . $student->id) ?>" class="btn-secondary px-6 rounded-md text-sm font-bold">
                        Batal
                    </a>
                    <button type="submit" class="btn-primary px-8 rounded-md text-sm font-bold shadow-lg shadow-primary-500/20">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/js/student_data.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const jSelect = document.getElementById('jurusan');
        const pSelect = document.getElementById('prodi');
        
        // Initial values from server
        const currentJurusan = "<?= old('jurusan', $student->jurusan) ?>";
        const currentProdi = "<?= old('prodi', $student->prodi) ?>";

        // Populate Jurusan
        Object.keys(POLSRI_DEPARTMENTS).forEach(j => {
            const option = document.createElement('option');
            option.value = j;
            option.textContent = j;
            if (j === currentJurusan) option.selected = true;
            jSelect.appendChild(option);
        });

        // Populate Prodi based on initial Jurusan
        if (currentJurusan) {
            updateProdiOptions(jSelect, pSelect, currentProdi);
        }

        // Listener for changes
        jSelect.addEventListener('change', () => {
            updateProdiOptions(jSelect, pSelect);
        });
    });
</script>
<?= $this->endSection() ?>
