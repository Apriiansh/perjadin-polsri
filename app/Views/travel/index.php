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

<!-- Summary Stats Cards -->
<?php if (isset($stats)): ?>
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total -->
        <div class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Perjadin</p>
                    <h3 class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($stats['total'] ?? 0) ?></h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition-transform group-hover:scale-110">
                    <i data-lucide="map" class="h-6 w-6"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-blue-500 opacity-20"></div>
        </div>

        <!-- Draft -->
        <div class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Draft / Belum Lengkap</p>
                    <h3 class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($stats['draft'] ?? 0) ?></h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-50 text-slate-600 transition-transform group-hover:scale-110">
                    <i data-lucide="file-edit" class="h-6 w-6"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-slate-400 opacity-20"></div>
        </div>

        <!-- Pending Verification -->
        <div class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Menunggu Verifikasi</p>
                    <h3 class="mt-1 text-2xl font-bold text-amber-600"><?= number_format($stats['pending'] ?? 0) ?></h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition-transform group-hover:scale-110">
                    <i data-lucide="shield-alert" class="h-6 w-6"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-amber-500 opacity-20"></div>
        </div>

        <!-- Completed -->
        <div class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Perjadin Selesai</p>
                    <h3 class="mt-1 text-2xl font-bold text-emerald-600"><?= number_format($stats['completed'] ?? 0) ?></h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition-transform group-hover:scale-110">
                    <i data-lucide="check-circle" class="h-6 w-6"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-emerald-500 opacity-20"></div>
        </div>
    </div>
<?php endif; ?>

<div class="card overflow-hidden p-0 border-none shadow-premium bg-white/80 backdrop-blur-md">
    <div class="overflow-x-auto">
        <table id="travelTable" class="w-full text-sm">
            <thead class="bg-slate-50/50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left font-bold uppercase tracking-wider">No</th>
                    <th class="px-4 py-3 text-left font-bold uppercase tracking-wider">Pegawai</th>
                    <th class="px-4 py-3 text-left font-bold uppercase tracking-wider">Surat Tugas</th>
                    <th class="px-4 py-3 text-left font-bold uppercase tracking-wider">Tujuan</th>
                    <th class="px-4 py-3 text-center font-bold uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center font-bold uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($travelRequests as $index => $req) : ?>
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-4 py-4 text-slate-500 font-medium"><?= $index + 1 ?></td>
                        <td class="px-4 py-4">
                            <div class="flex flex-col gap-1.5">
                                <?php if (!empty($req->members)): ?>
                                    <?php foreach ($req->members as $member): ?>
                                        <?php $isThisMember = ($member->user_id == auth()->id()); ?>
                                        <div class="flex items-center justify-between gap-3 px-2 py-1.5 bg-white border <?= $isThisMember ? 'border-primary-300 ring-1 ring-primary-100 shadow-md' : 'border-slate-200' ?> rounded-lg shadow-sm w-full max-w-[200px] group/member transition-all">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="flex h-7 w-7 shrink-0 <?= $isThisMember ? 'bg-primary-500 text-white' : 'bg-primary-50 text-primary-700' ?> rounded-full items-center justify-center text-[10px] font-bold border border-primary-100 uppercase">
                                                    <?= substr($member->employee_name ?? 'P', 0, 1) ?>
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-[11px] font-bold <?= $isThisMember ? 'text-primary-700' : 'text-slate-900' ?> truncate">
                                                        <?= esc($member->employee_name ?? '-') ?>
                                                        <?= $isThisMember ? ' (Anda)' : '' ?>
                                                    </span>
                                                    <span class="text-[9px] text-slate-500 font-mono"><?= esc($member->employee_nip ?? '-') ?></span>
                                                </div>
                                            </div>

                                            <?php if ($req->status === 'active' || $req->status === 'completed'): ?>
                                                <?php
                                                $isVerif = auth()->user()->inGroup('superadmin', 'verificator');

                                                // Determine visual status
                                                $docColor = 'slate';
                                                $docIcon = 'file-text';
                                                $docTitle = 'Belum ada dokumen';

                                                if ($member->uploaded_docs > 0) {
                                                    $docColor = 'amber';
                                                    $docIcon = 'file-up';
                                                    $docTitle = ($member->uploaded_docs) . ' Dokumen diunggah';
                                                }
                                                if ($member->verified_docs > 0) {
                                                    $docColor = $member->verified_docs === $member->total_docs && $member->total_docs > 0 ? 'emerald' : 'blue';
                                                    $docIcon = $member->verified_docs === $member->total_docs ? 'check-circle' : 'file-check';
                                                    $docTitle = $member->verified_docs . '/' . $member->total_docs . ' Terverifikasi';
                                                }

                                                $docUrl = $isVerif
                                                    ? base_url('documentation/' . $req->id . '/verification#member-' . $member->id)
                                                    : base_url('documentation/' . $req->id);
                                                ?>

                                                <?php if ($isVerif || $isThisMember): ?>
                                                    <a href="<?= $docUrl ?>"
                                                        class="flex h-6 w-6 items-center justify-center rounded-md bg-<?= $docColor ?>-50 text-<?= $docColor ?>-600 border border-<?= $docColor ?>-100 hover:bg-<?= $docColor ?>-100 transition-colors shrink-0"
                                                        title="<?= $docTitle ?>">
                                                        <i data-lucide="<?= $docIcon ?>" class="w-3.5 h-3.5"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <div class="flex h-6 w-6 items-center justify-center rounded-md bg-<?= $docColor ?>-50/50 text-<?= $docColor ?>-400 border border-<?= $docColor ?>-100/50 shrink-0" title="<?= $docTitle ?>">
                                                        <i data-lucide="<?= $docIcon ?>" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">Belum ada anggota</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900"><?= esc($req->no_surat_tugas ?? '-') ?></span>
                                <span class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <?= date('d M Y', strtotime($req->tgl_surat_tugas)) ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-slate-700 line-clamp-1 mb-1" title="<?= esc($req->perihal_surat_rujukan) ?>">
                                    <?= esc(mb_strimwidth($req->perihal_surat_rujukan ?? '-', 0, 45, '...')) ?>
                                </span>
                                <div class="flex items-center gap-1.5 text-slate-600">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-primary-500"></i>
                                    <span class="font-medium"><?= esc($req->destination_province ?? '-') ?></span>
                                </div>
                                <?php if (!empty($req->destination_city)) : ?>
                                    <span class="text-[11px] text-slate-500 ml-5"><?= esc($req->destination_city) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <?php
                            $badgeMap = [
                                'draft'     => ['bg-slate-100 text-slate-600 border-slate-200', 'bg-slate-400', 'Draft / Persiapan'],
                                'active'    => ['bg-blue-50 text-blue-600 border-blue-100', 'bg-blue-500', 'Aktif / Berjalan'],
                                'completed' => ['bg-emerald-50 text-emerald-600 border-emerald-100', 'bg-emerald-500', 'Selesai'],
                                'cancelled' => ['bg-rose-50 text-rose-600 border-rose-100', 'bg-rose-500', 'Dibatalkan'],
                            ];
                            $badge = $badgeMap[$req->status] ?? $badgeMap['draft'];
                            $isStaffOrVerif = auth()->user()->inGroup('superadmin', 'admin', 'verificator');
                            ?>
                            <div class="flex flex-col items-center gap-2">
                                <span class="dt-badge py-1.5 px-3 border <?= $badge[0] ?> items-center justify-center min-w-[120px]">
                                    <span class="dt-badge-dot <?= $badge[1] ?>"></span>
                                    <?= $badge[2] ?>
                                </span>

                                <?php if ($req->status === 'active'): ?>
                                    <!-- Personal Status for Member -->
                                    <?php if (isset($req->personal_stats)): ?>
                                        <?php
                                        $total = $req->personal_stats['total'];
                                        $up = $req->personal_stats['uploaded'];
                                        $ver = $req->personal_stats['verified'];
                                        $allDoneUser = ($ver === $total && $total > 0);
                                        $pctUser = $total > 0 ? round(($ver / $total) * 100) : 0;
                                        ?>
                                        <div class="flex flex-col items-center gap-1 w-full max-w-[120px]">
                                            <div class="w-full bg-slate-100 h-1 rounded-full overflow-hidden" title="Progres Anda">
                                                <div class="h-full <?= $allDoneUser ? 'bg-emerald-500' : 'bg-primary-500' ?>" style="width: <?= $pctUser ?>%"></div>
                                            </div>
                                            <span class="text-[9px] font-bold <?= $allDoneUser ? 'text-emerald-600' : 'text-primary-600' ?>">
                                                Dokumen Anda: <?= $ver ?>/<?= $total ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Team Total (Admin/Verif only or toggle) -->
                                    <?php if ($isStaffOrVerif): ?>
                                        <div class="mt-1 pt-1 border-t border-slate-100 w-full flex flex-wrap justify-center gap-1">
                                            <?php if (isset($req->uploaded_docs) && $req->uploaded_docs > 0): ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[9px] font-bold border border-amber-100 shadow-sm" title="Total dokumen baru tim">
                                                    <i data-lucide="file-up" class="w-2.5 h-2.5"></i>
                                                    <?= $req->uploaded_docs ?> Baru
                                                </span>
                                            <?php endif; ?>
                                            <?php if (isset($req->verified_docs) && $req->verified_docs > 0): ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-[9px] font-bold border border-emerald-100" title="Total dokumen terverifikasi tim">
                                                    <i data-lucide="check-check" class="w-2.5 h-2.5"></i>
                                                    <?= $req->verified_docs ?> OK
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <?php
                                $isStaff = auth()->user()->inGroup('superadmin', 'admin');
                                $isVerificator = auth()->user()->inGroup('verificator');
                                $uploadedDocs = $req->uploaded_docs ?? 0;
                                ?>

                                <!-- Selalu tampilkan "Show" -->
                                <a href="<?= base_url('travel/' . $req->id) ?>" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:text-primary-600 hover:bg-primary-50 transition-all active:scale-95 shadow-sm" title="Lihat Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>

                                <?php if ($req->status === 'draft' && $isStaff): ?>
                                    <a href="<?= base_url('travel/' . $req->id . '/enrichment') ?>" class="btn-primary flex h-9 items-center gap-2 px-4 py-2 text-xs font-bold shadow-sm hover:shadow-md active:scale-95 transition-all">
                                        <i data-lucide="clipboard-check" class="w-4 h-4 text-white/80"></i>
                                        <span>Lengkapi</span>
                                    </a>
                                <?php elseif ($req->status === 'active' || $req->status === 'completed'): ?>
                                    <?php if (($isStaff || $isVerificator) && $uploadedDocs > 0): ?>
                                        <a href="<?= base_url('documentation/' . $req->id . '/verification') ?>" class="btn-warning flex h-9 items-center gap-2 px-4 py-2 text-xs font-bold shadow-sm hover:shadow-md active:scale-95 transition-all">
                                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                                            <span>Verifikasi</span>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($req->personal_stats)): ?>
                                        <a href="<?= base_url('documentation/' . $req->id) ?>" class="btn-primary flex h-9 items-center gap-2 px-3 py-2 text-xs font-bold shadow-sm hover:shadow-md active:scale-95 transition-all" title="Kelola Dokumentasi Saya">
                                            <i data-lucide="file-up" class="w-4 h-4"></i>
                                            <span>Dokumentasi</span>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Secondary Actions for Staff -->
                                <?php if ($isStaff && $req->status === 'draft'): ?>
                                    <div class="flex gap-1.5 ml-2 border-l border-slate-100 pl-2">
                                        <a href="<?= base_url('travel/' . $req->id . '/edit') ?>" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Pengajuan">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <form action="<?= base_url('travel/' . $req->id . '/destroy') ?>" method="post" onsubmit="return confirm('Hapus pengajuan ini?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="<?= base_url('assets/js/travel.js') ?>" defer></script>
<?= $this->endSection() ?>