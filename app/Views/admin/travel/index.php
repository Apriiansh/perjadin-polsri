<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Manage Data Travel</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola data travel perjalanan dinas.</p>
    </div>

    <a href="<?= base_url('admin/travel/create') ?>" class="btn-primary inline-flex items-center gap-2 text-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Buat Request Perjalanan Dinas
    </a>
</div>

<!-- Summary badges -->
<div class="mb-5 flex flex-wrap gap-2">
    <span class="inline-flex items-center gap-1.5 rounded-md bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-800">
        <i data-lucide="user-lock" class="h-4 w-4"></i>
        Total: <?= number_format(count($travelRequests ?? [])) ?> travel
    </span>
</div>


<div class="card overflow-hidden p-0">
    <table id="travelTable" class="w-full text-sm">
        <thead>
            <tr>
                <th>No</th>
                <th>Pegawai</th>
                <th>Surat Tugas</th>
                <th>Tujuan</th>
                <th>Jadwal</th>
                <th>Total Biaya</th>
                <th class="text-center">Status</th>
                <th class="text-center font-bold">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($travelRequests as $index => $req) : ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <div class="flex flex-col gap-1">
                            <?php if (!empty($req->members)): ?>
                                <?php foreach ($req->members as $member): ?>
                                    <div class="flex flex-col p-1 border border-accent-600 rounded">
                                        <span class="font-mono text-[10px] font-bold text-primary-700"><?= esc($member->employee_nip ?? '-') ?></span>
                                        <span class="text-xs"><?= esc($member->employee_name ?? '-') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-xs text-slate-400 italic">Belum ada anggota</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-col">
                            <span><?= esc($req->no_surat_tugas ?? '-') ?></span>
                            <span class="text-xs text-slate-500"><?= esc($req->tgl_surat_tugas ?? '-') ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-col">
                            <span><?= esc($req->destination ?? '-') ?></span>
                            <span><?= esc($req->destination_province ?? '-') ?></span>
                            <?php if (!empty($req->destination_city)) : ?>
                                <span class="text-xs text-slate-500"><?= esc($req->destination_city) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-col">
                            <span><?= esc($req->departure_date ?? '-') ?></span>
                            <span class="text-xs text-slate-500"><?= esc($req->return_date ?? '-') ?></span>
                            <span class="text-xs font-bold text-primary-600"><?= esc($req->duration_days ?? 0) ?> hari</span>
                        </div>
                    </td>
                    <td class="whitespace-nowrap font-medium text-slate-700">Rp <?= number_format($req->total_budget ?? 0, 0, ',', '.') ?></td>
                    <td>
                        <?php if ($req->status == 'draft') : ?>
                            <span class="dt-badge p-2 bg-slate-200 text-slate-500">
                                <span class="dt-badge-dot bg-slate-500"></span>
                                Draft
                            </span>
                        <?php elseif ($req->status == 'pending') : ?>
                            <span class="dt-badge p-2 bg-yellow-200 text-yellow-500">
                                <span class="dt-badge-dot bg-yellow-500"></span>
                                Pending
                            </span>
                        <?php elseif ($req->status == 'approved') : ?>
                            <span class="dt-badge p-2 bg-emerald-200 text-emerald-500">
                                <span class="dt-badge-dot bg-emerald-500"></span>
                                Approved
                            </span>
                        <?php elseif ($req->status == 'verified') : ?>
                            <span class="dt-badge p-2 bg-blue-200 text-blue-500">
                                <span class="dt-badge-dot bg-blue-500"></span>
                                Verified
                            </span>
                        <?php elseif ($req->status == 'rejected') : ?>
                            <span class="dt-badge p-2 bg-red-200 text-red-500">
                                <span class="dt-badge-dot bg-red-500"></span>
                                Rejected
                            </span>
                        <?php elseif ($req->status == 'cancelled') : ?>
                            <span class="dt-badge p-2 bg-orange-200 text-orange-500">
                                <span class="dt-badge-dot bg-orange-500"></span>
                                Cancelled
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                        <div class="flex justify-center items-center gap-2">
                            <a href="<?= base_url('admin/travel/' . $req->id) ?>" class="rounded-md border p-1.5 text-blue-500 hover:bg-blue-50" title="Lihat Detail">
                                <i data-lucide="eye" class="h-4 w-4"></i>
                            </a>
                            <!-- Edit Travel -->
                            <a href="<?= base_url('admin/travel/' . $req->id . '/edit') ?>" class="rounded-md border p-1.5 text-slate-500 hover:bg-slate-50" title="Edit Travel">
                                <i data-lucide="edit" class="h-4 w-4"></i>
                            </a>
                            <!-- Delete Travel -->
                            <form action="<?= base_url('admin/travel/' . $req->id . '/destroy') ?>" method="post" onsubmit="return confirm('Yakin hapus travel <?= esc($req->no_surat_tugas ?? 'ini') ?>? Tindakan ini tidak dapat dibatalkan.')">
                                <?= csrf_field() ?>
                                <button type="submit" class="rounded-md border p-1.5 text-red-500 hover:bg-red-50" title="Hapus Travel">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="<?= base_url('assets/js/travel.js') ?>" defer></script>
<?= $this->endSection() ?>