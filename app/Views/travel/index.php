<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title ?? 'Pengajuan Perdin') ?></h1>
        <p class="mt-1 text-sm text-slate-500"><?= ($isStaff ?? false) ? 'Kelola data perjalanan dinas pegawai.' : 'Daftar perjalanan dinas Anda.' ?></p>
    </div>

    <?php if ($isStaff ?? false): ?>
        <a href="<?= base_url('travel/create') ?>" class="btn-primary inline-flex items-center gap-2 text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Input Perjalanan Dinas
        </a>
    <?php endif; ?>
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
                            <span class="text-xs text-slate-700"><?= esc(mb_strimwidth($req->perihal_surat_rujukan ?? '-', 0, 60, '...')) ?></span>
                            <span><?= esc($req->destination_province ?? '-') ?></span>
                            <?php if (!empty($req->destination_city)) : ?>
                                <span class="text-xs text-slate-500"><?= esc($req->destination_city) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($req->lokasi)) : ?>
                                <span class="text-xs text-slate-400 italic"><?= esc($req->lokasi) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php
                        $badgeMap = [
                            'draft'     => ['bg-slate-200 text-slate-600', 'bg-slate-500', 'Draft'],
                            'active'    => ['bg-emerald-200 text-emerald-600', 'bg-emerald-500', 'Aktif'],
                            'completed' => ['bg-blue-200 text-blue-600', 'bg-blue-500', 'Selesai'],
                            'cancelled' => ['bg-orange-200 text-orange-600', 'bg-orange-500', 'Dibatalkan'],
                        ];
                        $badge = $badgeMap[$req->status] ?? $badgeMap['draft'];
                        ?>
                        <div class="flex flex-col gap-1.5">
                            <span class="dt-badge py-1 px-2 <?= $badge[0] ?>">
                                <span class="dt-badge-dot <?= $badge[1] ?>"></span>
                                <?= $badge[2] ?>
                            </span>

                            <?php if ($req->status === 'active' && isset($req->total_docs) && $req->total_docs > 0): ?>
                                <?php if ($req->uploaded_docs > 0): ?>
                                    <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100 flex items-center gap-1 w-fit whitespace-nowrap">
                                        <i data-lucide="file-up" class="w-2.5 h-2.5"></i>
                                        <?= $req->uploaded_docs ?> Dokumen Baru
                                    </span>
                                <?php endif; ?>
                                <?php if ($req->verified_docs > 0): ?>
                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100 flex items-center gap-1 w-fit whitespace-nowrap">
                                        <i data-lucide="check-check" class="w-2.5 h-2.5"></i>
                                        <?= $req->verified_docs ?> Terverifikasi
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <div class="flex justify-center items-center gap-2">
                            <?php if (auth()->user()->inGroup('superadmin', 'admin') && $req->status === 'draft'): ?>
                                <a href="<?= base_url('travel/' . $req->id . '/enrichment') ?>" class="btn-primary w-full justify-center shadow-md hover:shadow-lg transition-all inline-flex items-center gap-1">
                                    <i data-lucide="clipboard-check" class="w-4 h-4"></i> Lengkapi
                                </a>
                            <?php endif; ?>
                            <?php if ($req->status === 'active'): ?>
                                <?php 
                                    $isVerifOnly = auth()->user()->inGroup('verificator') && !auth()->user()->inGroup('superadmin', 'admin');
                                    $btnLabel = $isVerifOnly ? 'Verifikasi' : 'Dokumentasi';
                                    $btnIcon = $isVerifOnly ? 'shield-check' : 'upload-cloud';
                                    $btnPath = $isVerifOnly ? 'documentation/' . $req->id . '/verification' : 'documentation/' . $req->id;
                                ?>
                                <a href="<?= base_url($btnPath) ?>" class="btn-warning w-full justify-center shadow-md hover:shadow-lg transition-all inline-flex items-center gap-1">
                                    <i data-lucide="<?= $btnIcon ?>" class="w-4 h-4"></i> <?= $btnLabel ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?= base_url('travel/' . $req->id) ?>" class="rounded-md border p-1.5 text-blue-500 hover:bg-blue-50" title="Lihat Detail">
                                <i data-lucide="eye" class="h-4 w-4"></i>
                            </a>
                            <?php if (($isStaff ?? false) && $req->status === 'draft'): ?>
                                <a href="<?= base_url('travel/' . $req->id . '/edit') ?>" class="rounded-md border p-1.5 text-slate-500 hover:bg-slate-50" title="Edit">
                                    <i data-lucide="edit" class="h-4 w-4"></i>
                                </a>
                                <form action="<?= base_url('travel/' . $req->id . '/destroy') ?>" method="post" onsubmit="return confirm('Yakin hapus pengajuan <?= esc($req->no_surat_tugas ?? 'ini') ?>? Tindakan ini tidak dapat dibatalkan.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="rounded-md border p-1.5 text-red-500 hover:bg-red-50" title="Hapus">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
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