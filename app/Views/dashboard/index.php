<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page header -->
<div class="mb-8">
    <h1 class="text-2xl font-extrabold text-slate-900">Dashboard</h1>
    <p class="mt-1 text-sm text-slate-500">Selamat datang, <span class="font-semibold text-accent-600"><?= esc(auth()->user()->username ?? 'User') ?></span>. Berikut ringkasan modul PERJADIN.</p>
</div>

<!-- Stat cards -->
<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <!-- Travel Requests -->
    <div class="card group relative overflow-hidden transition-shadow hover:shadow-md">
        <div class="absolute -right-3 -top-3 h-20 w-20 rounded-md bg-secondary-50 transition-transform group-hover:scale-110"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Perjalanan Dinas</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">0</p>
                <p class="mt-1 text-xs text-slate-400">Total pengajuan</p>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-secondary-100 text-secondary-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
            </span>
        </div>
    </div>

    <!-- Pending Verification -->
    <div class="card group relative overflow-hidden transition-shadow hover:shadow-md">
        <div class="absolute -right-3 -top-3 h-20 w-20 rounded-md bg-accent-50 transition-transform group-hover:scale-110"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Menunggu Verifikasi</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">0</p>
                <p class="mt-1 text-xs text-slate-400">Belum diproses</p>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-accent-100 text-accent-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
        </div>
    </div>

    <!-- Total Pegawai -->
    <div class="card group relative overflow-hidden transition-shadow hover:shadow-md">
        <div class="absolute -right-3 -top-3 h-20 w-20 rounded-md bg-primary-50 transition-transform group-hover:scale-110"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Pegawai</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= number_format($totalEmployees ?? 0) ?></p>
                <p class="mt-1 text-xs text-slate-400">Data tersinkronisasi</p>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-primary-100 text-primary-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            </span>
        </div>
    </div>

    <!-- Tariffs Active -->
    <div class="card group relative overflow-hidden transition-shadow hover:shadow-md">
        <div class="absolute -right-3 -top-3 h-20 w-20 rounded-md bg-green-50 transition-transform group-hover:scale-110"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tarif Aktif</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">0</p>
                <p class="mt-1 text-xs text-slate-400">Kategori biaya</p>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-green-100 text-green-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
            </span>
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="mt-8">
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">Aksi Cepat</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="<?= base_url('travel') ?>" class="card group flex items-center gap-4 transition-shadow hover:shadow-md">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-secondary-100 text-secondary-600 transition-colors group-hover:bg-secondary-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Buat Pengajuan Baru</p>
                <p class="text-xs text-slate-400">Ajukan perjalanan dinas</p>
            </div>
        </a>

        <a href="<?= base_url('admin/employees') ?>" class="card group flex items-center gap-4 transition-shadow hover:shadow-md">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-primary-100 text-primary-700 transition-colors group-hover:bg-primary-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Sinkronisasi Pegawai</p>
                <p class="text-xs text-slate-400">Update data dari API POLSRI</p>
            </div>
        </a>

        <a href="<?= base_url('admin/reports') ?>" class="card group flex items-center gap-4 transition-shadow hover:shadow-md">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-accent-100 text-accent-600 transition-colors group-hover:bg-accent-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Lihat Laporan</p>
                <p class="text-xs text-slate-400">Rekap perjalanan dinas</p>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>
