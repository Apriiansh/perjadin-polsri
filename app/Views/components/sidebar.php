<?php
$current = service('uri')->getPath();
$userGroups  = auth()->user()?->getGroups() ?? [];
$config = config('AuthGroups');
$groupTitles = array_map(fn($group) => $config->groups[$group]['title'] ?? $group, $userGroups);
$isAdmin = in_array('superadmin', $userGroups) || in_array('admin', $userGroups);
$isVerif = in_array('verificator', $userGroups) || $isAdmin;
?>
<aside class="flex h-full flex-col border-r border-surface-200 bg-white lg:min-h-screen">
  <!-- Brand header -->
  <div class="flex h-16 shrink-0 items-center justify-between border-b border-surface-100 px-5">
    <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-2.5">
      <img src="<?= base_url('img/logo-polsri.png') ?>" alt="logo polsri" class="inline-flex h-9 w-9 items-center justify-center rounded-md shadow-sm" />
      <div>
        <p class="text-sm font-extrabold leading-none text-slate-900">SISPERDIN</p>
        <p class="text-[10px] font-semibold uppercase tracking-widest text-accent-600">POLSRI</p>
      </div>
    </a>

    <button id="sidebarCloseBtn" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-surface-100 hover:text-slate-600 lg:hidden" aria-label="Tutup menu">
      <i data-lucide="x" class="h-5 w-5"></i>
    </button>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
    <!-- Section: Utama -->
    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Utama</p>

    <a href="<?= base_url('dashboard') ?>" class="sidebar-link group <?= str_starts_with($current, 'dashboard') ? 'active' : '' ?>">
      <i data-lucide="layout-grid" class="sidebar-icon"></i>
      Dashboard
    </a>

    <a href="<?= base_url('travel') ?>" class="sidebar-link group <?= str_starts_with($current, 'travel') ? 'active' : '' ?>">
      <i data-lucide="send" class="sidebar-icon"></i>
      Pengajuan Perdin
    </a>

    <?php if ($isVerif): ?>
      <!-- Section: Verifikasi -->
      <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Verifikasi</p>

      <a href="<?= base_url('verification') ?>" class="sidebar-link group <?= str_starts_with($current, 'verification') ? 'active' : '' ?>">
        <i data-lucide="shield-check" class="sidebar-icon"></i>
        Verifikasi Perdin
      </a>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
      <!-- Section: Admin -->
      <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Administrasi</p>

      <a href="<?= base_url('admin/employees') ?>" class="sidebar-link group <?= str_starts_with($current, 'admin/employees') ? 'active' : '' ?>">
        <i data-lucide="users" class="sidebar-icon"></i>
        Manage Pegawai
      </a>

      <a href="<?= base_url('admin/users') ?>" class="sidebar-link group <?= str_starts_with($current, 'admin/users') ? 'active' : '' ?>">
        <i data-lucide="user-cog" class="sidebar-icon"></i>
        Manage User
      </a>

      <a href="<?= base_url('admin/tariffs') ?>" class="sidebar-link group <?= str_starts_with($current, 'admin/tariffs') ? 'active' : '' ?>">
        <i data-lucide="banknote" class="sidebar-icon"></i>
        Tarif Biaya
      </a>

      <a href="<?= base_url('admin/signatories') ?>" class="sidebar-link group <?= str_starts_with($current, 'admin/signatories') ? 'active' : '' ?>">
        <i data-lucide="pen-tool" class="sidebar-icon"></i>
        Penandatangan
      </a>

      <a href="<?= base_url('admin/reports') ?>" class="sidebar-link group <?= str_starts_with($current, 'admin/reports') ? 'active' : '' ?>">
        <i data-lucide="bar-chart-3" class="sidebar-icon"></i>
        Laporan
      </a>
    <?php endif; ?>
  </nav>

  <!-- Bottom user card (desktop only) -->
  <div class="hidden shrink-0 border-t border-surface-100 p-4 lg:block">
    <div class="flex items-center gap-3">
      <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-linear-to-br from-accent-400 to-secondary-400 text-[11px] font-bold text-white">
        <?= strtoupper(mb_substr(esc(auth()->user()->username ?? 'U'), 0, 2)) ?>
      </span>
      <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-slate-800"><?= esc(auth()->user()->username ?? 'User') ?></p>
        <p class="truncate text-[11px] text-slate-400"><?= esc(implode(', ', $groupTitles)) ?></p>
      </div>
    </div>
  </div>
</aside>