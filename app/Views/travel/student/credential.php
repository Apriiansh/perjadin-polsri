<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-lg mx-auto">
    <!-- Warning Banner -->
    <div class="mb-6 flex items-start gap-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <i data-lucide="triangle-alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500"></i>
        <span><strong>Penting!</strong> Salin dan simpan kredensial ini. Password <strong>hanya ditampilkan sekali ini saja</strong> demi alasan keamanan.</span>
    </div>

    <div class="card p-8 border-none shadow-premium bg-white">
        <!-- Header -->
        <div class="mb-8 flex items-center gap-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 shadow-sm border border-emerald-200/50">
                <i data-lucide="user-check" class="h-6 w-6 text-emerald-600"></i>
            </span>
            <div>
                <h2 class="text-xl font-black text-slate-900 leading-tight">Akun Mahasiswa Aktif</h2>
                <p class="text-sm text-slate-500 font-medium">Kredensial login untuk <?= esc($credential['name']) ?></p>
            </div>
        </div>

        <!-- Credential Box -->
        <div class="space-y-4 rounded-xl border border-slate-800 bg-slate-900 p-6 font-mono shadow-2xl">
            <!-- Username -->
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1 overflow-hidden">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Username Login</p>
                    <p id="valUsername" class="text-lg font-bold text-emerald-400 tabular-nums truncate"><?= esc($credential['username']) ?></p>
                </div>
                <button onclick="copyToClipboard('<?= esc($credential['username']) ?>', this)" class="h-10 w-10 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:border-emerald-500/50 hover:bg-emerald-500/10 hover:text-emerald-400 transition-all group shrink-0" title="Salin Username">
                    <i data-lucide="copy" class="h-4 w-4"></i>
                </button>
            </div>

            <div class="border-t border-slate-800"></div>

            <!-- Password -->
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1 overflow-hidden">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Password Baru</p>
                    <p id="valPassword" class="text-lg font-bold text-emerald-400 tabular-nums truncate"><?= esc($credential['password']) ?></p>
                </div>
                <button onclick="copyToClipboard('<?= esc($credential['password']) ?>', this)" class="h-10 w-10 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:border-emerald-500/50 hover:bg-emerald-500/10 hover:text-emerald-400 transition-all group shrink-0" title="Salin Password">
                    <i data-lucide="copy" class="h-4 w-4"></i>
                </button>
            </div>
        </div>

        <p class="mt-6 text-[10px] text-center text-slate-400 italic">Gunakan username di atas untuk masuk ke sistem Polsri Pay.</p>

        <!-- Actions -->
        <div class="mt-8 flex flex-col gap-3">
            <button onclick="copyAll()" id="copyAllBtn" class="flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-100 text-sm font-bold text-slate-700 hover:bg-slate-200 transition-all shadow-sm">
                <i data-lucide="clipboard-copy" class="h-4 w-4"></i>
                Salin Semua Info
            </button>
            <a href="<?= base_url('travel/student') ?>" class="flex h-11 items-center justify-center gap-2 rounded-lg bg-indigo-600 text-sm font-bold text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 hover:scale-[1.02] active:scale-95 transition-all">
                <i data-lucide="check-circle" class="h-4 w-4"></i>
                Selesai & Lanjutkan
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
    function copyToClipboard(text, btn) {
        if (!text) return;
        
        navigator.clipboard.writeText(text.trim()).then(() => {
            const originalHtml = btn.innerHTML;
            
            btn.innerHTML = '<i data-lucide="check" class="h-4 w-4"></i>';
            lucide.createIcons();
            btn.classList.add('border-emerald-500', 'bg-emerald-500/10', 'text-emerald-400');
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                lucide.createIcons();
                btn.classList.remove('border-emerald-500', 'bg-emerald-500/10', 'text-emerald-400');
            }, 2000);
        }).catch(err => {
            console.error('Gagal menyalin:', err);
        });
    }

    function copyAll() {
        const username = document.getElementById('valUsername').textContent.trim();
        const password = document.getElementById('valPassword').textContent.trim();
        const text = `Kredensial Login Polsri Pay\nUsername: ${username}\nPassword: ${password}`;
        
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('copyAllBtn');
            const originalContent = btn.innerHTML;
            
            btn.innerHTML = '<i data-lucide="check" class="h-4 w-4"></i> Info Tersalin!';
            btn.classList.add('bg-emerald-100', 'text-emerald-700');
            btn.classList.remove('bg-slate-100', 'text-slate-700');
            lucide.createIcons();
            
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.classList.remove('bg-emerald-100', 'text-emerald-700');
                btn.classList.add('bg-slate-100', 'text-slate-700');
                lucide.createIcons();
            }, 2000);
        });
    }
</script>
<?= $this->endSection() ?>
