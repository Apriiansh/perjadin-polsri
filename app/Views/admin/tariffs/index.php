<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Manage Data Tarif</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola data tarif perjalanan dinas.</p>
    </div>

    <a href="<?= base_url('admin/tariffs/create') ?>" class="btn-primary inline-flex items-center gap-2 text-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Tambah Data Tarif
    </a>
</div>

<!-- Summary badges -->
<div class="mb-5 flex flex-wrap gap-2">
    <span class="inline-flex items-center gap-1.5 rounded-md bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-800">
        <i data-lucide="user-lock" class="h-4 w-4"></i>
        Total: <?= number_format(count($tariffs ?? [])) ?> tarif
    </span>
</div>

<div class="card overflow-hidden p-0">
    <table id="tariffsTable" class="w-full text-sm">
        <thead>
            <tr>
                <th>Provinsi & Kota</th>
                <th>Tingkat Biaya</th>
                <th>Uang Harian</th>
                <th>Uang Representasi</th>
                <th>Penginapan</th>
                <th class="text-center">Status</th>
                <th>Tahun Berlaku</th>
                <th class="text-center font-bold">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tariffs ?? [] as $tariff) : ?>
                <tr>
                    <td>
                        <div class="flex flex-col">
                            <span class="font-mono text-xs font-bold text-primary-700"><?= esc($tariff->province ?? '-') ?></span>
                            <?php if (!empty($tariff->city)) : ?>
                                <span class="text-xs text-slate-500"><?= esc($tariff->city) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600 shadow-sm">
                            Tingkat <?= esc($tariff->tingkat_biaya ?? '-') ?>
                        </span>
                    </td>
                    <td class="text-slate-600">Rp <?= number_format($tariff->uang_harian ?? 0, 0, ',', '.') ?></td>
                    <td class="text-slate-600">Rp <?= number_format($tariff->uang_representasi ?? 0, 0, ',', '.') ?></td>
                    <td>
                        <div class="flex flex-col">
                            <span class="text-slate-600">Rp <?= number_format($tariff->penginapan ?? 0, 0, ',', '.') ?></span>
                            <span class="text-xs text-slate-400"><?= esc($tariff->jenis_penginapan ?? '') ?></span>
                        </div>
                    </td>
                    <td>
                        <?php if ($tariff->is_active == 0) : ?>
                            <span class="dt-badge dt-badge-inactive">
                                <span class="dt-badge-dot bg-red-500"></span>
                                Nonaktif
                            </span>
                        <?php else : ?>
                            <span class="dt-badge dt-badge-active">
                                <span class="dt-badge-dot bg-emerald-500"></span>
                                Aktif
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-xs text-slate-400">
                        <?= esc($tariff->tahun_berlaku ?? '-') ?>
                    </td>
                    <td class="text-center flex justify-center gap-2">
                        <!-- Edit Tarif -->
                        <a href="<?= base_url('admin/tariffs/' . $tariff->id . '/edit') ?>" class="rounded-md border p-1.5 text-slate-500 hover:bg-slate-50" title="Edit Tarif">
                            <i data-lucide="edit" class="h-4 w-4"></i>
                        </a>
                        <!-- Delete Tarif -->
                        <form action="<?= base_url('admin/tariffs/' . $tariff->id . '/destroy') ?>" method="post" onsubmit="return confirm('Yakin hapus tarif <?= esc($tariff->province) ?>? Tindakan ini tidak dapat dibatalkan.')">
                            <?= csrf_field() ?>
                            <button type="submit" class="rounded-md border p-1.5 text-red-500 hover:bg-red-50" title="Hapus Tarif">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="<?= base_url('assets/js/tariffs.js') ?>" defer></script>
<?= $this->endSection() ?>