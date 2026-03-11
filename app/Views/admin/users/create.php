<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card max-w-lg mx-auto">
    <h2 class="text-xl font-bold">Buat Akun Pegawai</h2>

    <!-- Info Box Pegawai -->
    <div class="bg-surface-50 p-4 border rounded-md mt-4 text-sm space-y-1">
        <p class="font-semibold text-slate-800"><?= esc($employee['name']) ?></p>
        <p class="font-mono text-xs text-slate-400"><?= esc($employee['nip']) ?></p>
    </div>

    <!-- Form -->
    <form action="<?= base_url('admin/users/store') ?>" method="post" class="mt-4 space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="employee_id" value="<?= esc($employee['id']) ?>">

        <!-- Email + Generate Button -->
        <div>
            <label class="form-label" for="email">Alamat Email <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
                <input type="email" id="email" name="email" class="input-control" placeholder="Masukkan atau generate email..." required>
                <button type="button" id="generateEmailBtn" onclick="generateEmail()"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-md border border-secondary-200 bg-white px-3 py-2 text-xs font-semibold text-secondary-700 hover:bg-secondary-50 transition-colors"
                    title="Generate email otomatis dari nama pegawai">
                    <i data-lucide="wand-sparkles" class="h-3.5 w-3.5"></i>
                    Generate
                </button>
            </div>
            <p class="mt-1.5 text-xs text-slate-400">Klik <strong>Generate</strong> untuk buat email otomatis dari nama pegawai, atau isi manual.</p>
        </div>

        <!-- Role -->
        <div>
            <label class="form-label" for="group">Pilih Hak Akses (Role)</label>
            <select id="group" name="group" class="input-control" required>
                <?php foreach ($availableGroups as $key => $info): ?>
                    <option value="<?= esc($key) ?>"><?= esc($info['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="<?= base_url('admin/users') ?>" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan Akun
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
    // Nama pegawai dari PHP → JS
    const rawName = <?= json_encode($employee['name']) ?>;

    function generateEmail() {
        // Pola gelar akademik/profesional yang umum di Indonesia
        const titlePattern = /\b(ir|dr|drs|drh|prof|H|Hj|S\.Kom|S\.T|S\.E|S\.H|S\.Pd|S\.Si|S\.Sos|S\.Hum|S\.Farm|S\.Ag|M\.Kom|M\.T|M\.Si|M\.Sc|M\.A|M\.Ed|M\.Hum|M\.Farm|Ph\.D|D\.Sc|ST|SE|SH|SKom|MKom|MT|MSi)\b\.?/gi;

        // Hapus gelar, hapus karakter non-huruf (kecuali spasi), rapikan spasi
        let name = rawName
            .replace(titlePattern, '') // Hapus gelar
            .replace(/[^a-zA-Z\s]/g, '') // Hapus titik, koma, dll
            .trim()
            .replace(/\s+/g, ' '); // Normalkan spasi

        // Split jadi kata-kata, ambil yang panjangnya > 1 (buang sisa gelar 1 huruf)
        const words = name.split(' ').filter(w => w.length > 1);

        if (words.length === 0) {
            alert('Tidak bisa generate email dari nama ini. Isi secara manual.');
            return;
        }

        const email = words.join('.').toLowerCase() + '@polsri.ac.id';
        document.getElementById('email').value = email;

        // Feedback visual on the button
        const btn = document.getElementById('generateEmailBtn');
        btn.innerHTML = '<i data-lucide="check" class="h-3.5 w-3.5"></i> Terbuat';
        btn.classList.add('border-emerald-300', 'text-emerald-700', 'bg-emerald-50');
        lucide.createIcons();
        setTimeout(() => {
            btn.innerHTML = '<i data-lucide="wand-sparkles" class="h-3.5 w-3.5"></i> Generate';
            btn.classList.remove('border-emerald-300', 'text-emerald-700', 'bg-emerald-50');
            lucide.createIcons();
        }, 2000);
    }
</script>
<?= $this->endSection() ?>