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
        <div class="absolute -right-3 -top-3 h-20 w-20 rounded-md bg-secondary-200 transition-transform group-hover:scale-110"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Perjalanan Dinas</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">0</p>
                <p class="mt-1 text-xs text-slate-400">Total pengajuan</p>
            </div>
            <i data-lucide="send" class="sidebar-icon text-slate-900"></i>
        </div>
    </div>

    <!-- Pending Verification -->
    <div class="card group relative overflow-hidden transition-shadow hover:shadow-md">
        <div class="absolute -right-3 -top-3 h-20 w-20 rounded-md bg-primary-200 transition-transform group-hover:scale-110"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Menunggu Verifikasi</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">0</p>
                <p class="mt-1 text-xs text-slate-400">Belum diproses</p>
            </div>
            <i data-lucide="clock" class="sidebar-icon text-slate-900"></i>
        </div>
    </div>

    <!-- Tariffs Active -->
    <div class="card group relative overflow-hidden transition-shadow hover:shadow-md">
        <div class="absolute -right-3 -top-3 h-20 w-20 rounded-md bg-emerald-200 transition-transform group-hover:scale-110"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tarif Aktif</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">0</p>
                <p class="mt-1 text-xs text-slate-400">Kategori biaya</p>
            </div>
            <i data-lucide="plane-takeoff" class="sidebar-icon text-slate-900"></i>
        </div>
    </div>

    <!-- Total Pegawai -->
    <div class="card group relative overflow-hidden transition-shadow hover:shadow-md">
        <div class="absolute -right-3 -top-3 h-20 w-20 rounded-md bg-accent-200 transition-transform group-hover:scale-110"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Pegawai</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= number_format($totalEmployees ?? 0) ?></p>
                <p class="mt-1 text-xs text-slate-400">Data tersinkronisasi</p>
            </div>
            <i data-lucide="users" class="sidebar-icon text-slate-900"></i>
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="mt-8">
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">Aksi Cepat</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="<?= base_url('travel') ?>" class="card group flex items-center gap-4 transition-shadow hover:shadow-md">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-secondary-100 text-secondary-600 transition-colors group-hover:bg-secondary-200">
                <i data-lucide="plus" class="sidebar-icon text-secondary-900"></i>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Buat Pengajuan Baru</p>
                <p class="text-xs text-slate-400">Ajukan perjalanan dinas</p>
            </div>
        </a>

        <a href="<?= base_url('admin/employees') ?>" class="card group flex items-center gap-4 transition-shadow hover:shadow-md">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-primary-100 text-primary-700 transition-colors group-hover:bg-primary-200">
                <i data-lucide="cloud-sync" class="sidebar-icon text-primary-900"></i>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Sinkronisasi Pegawai</p>
                <p class="text-xs text-slate-400">Update data dari API POLSRI</p>
            </div>
        </a>

        <a href="<?= base_url('admin/reports') ?>" class="card group flex items-center gap-4 transition-shadow hover:shadow-md">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-accent-100 text-accent-600 transition-colors group-hover:bg-accent-200">
                <i data-lucide="chart-no-axes-column-increasing" class="sidebar-icon text-accent-900"></i>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Lihat Laporan</p>
                <p class="text-xs text-slate-400">Rekap perjalanan dinas</p>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>