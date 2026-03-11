<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-lg mx-auto">
    <!-- Warning Banner -->
    <div class="mb-6 flex items-start gap-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <i data-lucide="triangle-alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500"></i>
        <span><strong>Perhatian!</strong> Simpan kredensial ini sebelum meninggalkan halaman. Password <strong>tidak akan ditampilkan lagi</strong> setelah halaman ini ditutup.</span>
    </div>

    <div class="card">
        <!-- Header -->
        <div class="mb-6 flex items-center gap-3">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-md bg-emerald-100">
                <i data-lucide="shield-check" class="h-5 w-5 text-emerald-600"></i>
            </span>
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Akun Berhasil Dibuat</h2>
                <p class="text-sm text-slate-500">Berikut kredensial login yang telah di-generate.</p>
            </div>
        </div>

        <!-- Credential Box -->
        <div class="space-y-3 rounded-md border border-surface-200 bg-slate-900 p-5 font-mono">
            <!-- Username -->
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Username</p>
                    <p id="credUsername" class="mt-0.5 text-base text-emerald-400"><?= esc($credential['username']) ?></p>
                </div>
                <button onclick="copyToClipboard('credUsername', this)" class="rounded-md border border-slate-600 p-1.5 text-slate-400 hover:border-emerald-500 hover:text-emerald-400 transition-colors" title="Salin Username">
                    <i data-lucide="copy" class="h-4 w-4"></i>
                </button>
            </div>

            <div class="border-t border-slate-700"></div>

            <!-- Password -->
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Password</p>
                    <p id="credPassword" class="mt-0.5 text-base text-emerald-400"><?= esc($credential['password']) ?></p>
                </div>
                <button onclick="copyToClipboard('credPassword', this)" class="rounded-md border border-slate-600 p-1.5 text-slate-400 hover:border-emerald-500 hover:text-emerald-400 transition-colors" title="Salin Password">
                    <i data-lucide="copy" class="h-4 w-4"></i>
                </button>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button onclick="copyAll()" id="copyAllBtn" class="btn-secondary inline-flex items-center justify-center gap-2">
                <i data-lucide="clipboard-copy" class="h-4 w-4"></i>
                Salin Semua
            </button>
            <a href="<?= base_url('admin/users') ?>" class="btn-primary inline-flex items-center justify-center gap-2">
                <i data-lucide="check" class="h-4 w-4"></i>
                Selesai
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
    function copyToClipboard(elementId, btn) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            const icon = btn.querySelector('i');
            icon.setAttribute('data-lucide', 'check');
            lucide.createIcons();
            btn.classList.add('border-emerald-500', 'text-emerald-400');
            setTimeout(() => {
                icon.setAttribute('data-lucide', 'copy');
                lucide.createIcons();
            }, 2000);
        });
    }

    function copyAll() {
        const username = document.getElementById('credUsername').innerText;
        const password = document.getElementById('credPassword').innerText;
        const text = `Username: ${username}\nPassword: ${password}`;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('copyAllBtn');
            btn.innerHTML = '<i data-lucide="check" class="h-4 w-4"></i> Tersalin!';
            lucide.createIcons();
            setTimeout(() => {
                btn.innerHTML = '<i data-lucide="clipboard-copy" class="h-4 w-4"></i> Salin Semua';
                lucide.createIcons();
            }, 2000);
        });
    }
</script>
<?= $this->endSection() ?>