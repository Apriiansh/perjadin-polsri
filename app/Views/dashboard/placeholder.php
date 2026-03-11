<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card max-w-3xl">
    <h1 class="text-xl font-extrabold text-slate-900">Modul <?= esc(ucfirst($module ?? '')) ?></h1>
    <p class="mt-2 text-sm text-slate-600">Halaman ini disiapkan sebagai placeholder agar navigasi menu sesuai rancangan PERJADIN tetap utuh sambil fitur detail dibangun bertahap.</p>
</div>
<?= $this->endSection() ?>
