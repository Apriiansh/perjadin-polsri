<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'PERJADIN') ?> — Sisperdin POLSRI</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?= $this->renderSection('headStyles') ?>
</head>

<body class="bg-surface-50 text-slate-900">
    <!-- Mobile sidebar backdrop -->
    <div id="sidebarBackdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 backdrop-blur-sm transition-opacity duration-300 lg:hidden"></div>

    <div class="min-h-screen lg:grid lg:grid-cols-[264px_1fr]">
        <!-- Sidebar drawer -->
        <div id="sidebarDrawer" class="fixed inset-y-0 left-0 z-40 w-66 -translate-x-full transition-transform duration-300 ease-in-out lg:static lg:z-auto lg:w-auto lg:translate-x-0">
            <?= $this->include('components/sidebar') ?>
        </div>

        <!-- Main content area -->
        <div class="flex min-w-0 flex-col">
            <?= $this->include('components/navbar') ?>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <!-- Flash messages -->
                <?php if (session('success')): ?>
                    <div class="flash-msg group mb-4 flex items-start gap-3 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 animate-in" role="alert">
                        <span class="flex-1"><?= esc(session('success')) ?></span>
                        <button type="button" onclick="this.closest('.flash-msg').remove()" class="ml-auto shrink-0 text-green-400 hover:text-green-600 transition-colors" aria-label="Tutup">
                            <i data-lucide="circle-check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (session('error')): ?>
                    <div class="flash-msg group mb-4 flex items-start gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 animate-in" role="alert">
                        <span class="flex-1"><?= session('error') ?></span>
                        <button type="button" onclick="this.closest('.flash-msg').remove()" class="ml-auto shrink-0 text-red-400 hover:text-red-600 transition-colors" aria-label="Tutup">
                            <i data-lucide="circle-alert" class="mt-0.5 h-5 w-5 shrink-0 text-red-500"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (session('warning')): ?>
                    <div class="flash-msg group mb-4 flex items-start gap-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 animate-in" role="alert">
                        <span class="flex-1"><?= session('warning') ?></span>
                        <button type="button" onclick="this.closest('.flash-msg').remove()" class="ml-auto shrink-0 text-amber-400 hover:text-amber-600 transition-colors" aria-label="Tutup">
                            <i data-lucide="triangle-alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </main>

            <!-- Footer -->
            <footer class="border-t border-surface-200 px-4 py-4 sm:px-6 lg:px-8">
                <p class="text-center text-xs text-slate-400">&copy; <?= date('Y') ?> Sisperdin — Politeknik Negeri Sriwijaya</p>
            </footer>
        </div>
    </div>

    <!-- Lucide Icons Core -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- App Scripts -->
    <script src="<?= base_url('assets/js/app-layout.js') ?>" defer></script>
    <?= $this->renderSection('pageScripts') ?>
</body>

</html>