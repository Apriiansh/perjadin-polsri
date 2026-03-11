<?php
$username = esc(auth()->user()->username ?? 'User');
$userGroups   = auth()->user()?->getGroups() ?? [];
$config = config('AuthGroups');
$groupTitles = array_map(fn($group) => $config->groups[$group]['title'] ?? $group, $userGroups);
$initials = strtoupper(mb_substr($username, 0, 2));
?>
<header class="sticky top-0 z-20 border-b border-surface-200 bg-white/80 backdrop-blur-md">
    <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <!-- Left: hamburger + breadcrumb/title -->
        <div class="flex items-center gap-3">
            <button id="sidebarOpenBtn" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md text-slate-500 transition-colors hover:bg-surface-100 hover:text-slate-700 lg:hidden" aria-label="Buka menu">
                <i data-lucide="menu" class="h-5 w-5"></i>
            </button>

            <div class="hidden sm:block">
                <h2 class="text-sm font-bold text-slate-800">Sistem Perjalanan Dinas</h2>
                <p class="text-xs text-slate-400">Politeknik Negeri Sriwijaya</p>
            </div>
        </div>

        <!-- Right: user profile area -->
        <div class="relative flex items-center gap-2">
            <!-- User info + avatar button -->
            <button id="userMenuBtn" type="button" class="group flex items-center gap-3 rounded-md px-2 py-1.5 transition-colors hover:bg-surface-100">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-slate-800"><?= $username ?></p>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-accent-600"><?= esc(implode(', ', $groupTitles)) ?></p>
                </div>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-linear-to-br from-accent-400 to-secondary-400 text-xs font-bold text-white shadow-sm">
                    <?= $initials ?>
                </span>
                <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400 transition-transform group-aria-expanded:rotate-180"></i>
            </button>

            <!-- Dropdown -->
            <div id="userMenuDropdown" class="absolute right-0 top-full mt-1 hidden w-52 rounded-md border border-surface-200 bg-white py-1 shadow-lg ring-1 ring-black/5">
                <div class="border-b border-surface-100 px-4 py-2.5 sm:hidden">
                    <p class="text-sm font-semibold text-slate-800"><?= $username ?></p>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-accent-600"><?= esc(implode(', ', $userGroups)) ?></p>
                </div>
                <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 transition-colors hover:bg-surface-50">
                    <i data-lucide="user" class="h-4 w-4 text-slate-400"></i>
                    Profil Saya
                </a>
                <hr class="my-1 border-surface-100">
                <a href="<?= url_to('logout') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 transition-colors hover:bg-red-50">
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                    Keluar
                </a>
            </div>
        </div>
    </div>
</header>