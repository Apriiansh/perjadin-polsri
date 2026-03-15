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
                                        <div class="flex items-center gap-2 px-2 py-1 bg-white border border-slate-200 rounded-lg shadow-sm w-fit">
                                            <div class="flex h-6 w-6 shrink-0 bg-primary-100 text-primary-700 rounded-full items-center justify-center text-[10px] font-bold">
                                                <?= strtoupper(substr($member->employee_name ?? 'P', 0, 1)) ?>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-[11px] font-bold text-slate-900 truncate"><?= esc($member->employee_name ?? '-') ?></span>
                                                <span class="text-[9px] text-slate-500 font-mono"><?= esc($member->employee_nip ?? '-') ?></span>
                                            </div>
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
                            ?>
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="dt-badge py-1.5 px-3 border <?= $badge[0] ?> items-center justify-center min-w-[120px]">
                                    <span class="dt-badge-dot <?= $badge[1] ?>"></span>
                                    <?= $badge[2] ?>
                                </span>

                                <?php if ($req->status === 'active'): ?>
                                    <div class="flex flex-wrap justify-center gap-1 mt-1">
                                        <?php if (isset($req->uploaded_docs) && $req->uploaded_docs > 0): ?>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[10px] font-bold border border-amber-100 shadow-sm" title="Ada dokumen baru yang perlu diperiksa">
                                                <i data-lucide="file-warning" class="w-2.5 h-2.5"></i>
                                                <?= $req->uploaded_docs ?> Baru
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($req->verified_docs) && $req->verified_docs > 0): ?>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100" title="Dokumen yang sudah diverifikasi">
                                                <i data-lucide="check-check" class="w-2.5 h-2.5"></i>
                                                <?= $req->verified_docs ?> OK
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <?php 
                                    $isStaff = auth()->user()->inGroup('superadmin', 'admin');
                                    $isVerificator = auth()->user()->inGroup('verificator');
                                    $totalDocs = $req->total_docs ?? 0;
                                    $uploadedDocs = $req->uploaded_docs ?? 0;
                                    $verifiedDocs = $req->verified_docs ?? 0;
                                    $isFullyVerified = ($totalDocs > 0 && $verifiedDocs === $totalDocs);
                                ?>

                                <!-- Primary Action Button -->
                                <?php if ($req->status === 'draft' && $isStaff): ?>
                                    <a href="<?= base_url('travel/' . $req->id . '/enrichment') ?>" class="btn-primary flex h-9 items-center gap-2 px-4 py-2 text-xs font-bold shadow-sm hover:shadow-md active:scale-95 transition-all">
                                        <i data-lucide="clipboard-check" class="w-4 h-4 text-white/80"></i>
                                        <span>Lengkapi Data</span>
                                    </a>
                                <?php elseif ($req->status === 'active'): ?>
                                    <?php if ($isStaff || $isVerificator): ?>
                                        <?php if ($uploadedDocs > 0): ?>
                                            <a href="<?= base_url('documentation/' . $req->id . '/verification') ?>" class="btn-warning flex h-9 items-center gap-2 px-4 py-2 text-xs font-bold shadow-sm hover:shadow-md active:scale-95 transition-all">
                                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                                <span>Verifikasi</span>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('travel/' . $req->id) ?>" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors" title="<?= $isFullyVerified ? 'Terverifikasi' : 'Lihat Detail' ?>">
                                                <i data-lucide="<?= $isFullyVerified ? 'check-circle' : 'eye' ?>" class="w-4 h-4 <?= $isFullyVerified ? 'text-emerald-500' : '' ?>"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Dosen / Lecturer -->
                                        <?php if ($isFullyVerified): ?>
                                            <a href="<?= base_url('travel/' . $req->id) ?>" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Selesai Verifikasi">
                                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
                                            </a>
                                        <?php elseif ($totalDocs > 0): ?>
                                            <a href="<?= base_url('documentation/' . $req->id) ?>" class="btn-primary flex h-9 items-center gap-2 px-4 py-2 text-xs font-bold shadow-sm hover:shadow-md active:scale-95 transition-all whitespace-nowrap">
                                                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                                <span>Dokumentasi</span>
                                            </a>
                                        <?php else: ?>
                                            <div class="flex h-9 items-center gap-2 px-3 py-2 text-xs font-medium border border-slate-200 text-slate-400 rounded-lg bg-slate-50/50 cursor-help" title="Menunggu checklist verifikasi dari Keuangan">
                                                <i data-lucide="hourglass" class="w-4 h-4 animate-pulse"></i>
                                                <span>Antre</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?= base_url('travel/' . $req->id) ?>" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors" title="Lihat Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Secondary Actions -->
                                <?php if ($isStaff && $req->status === 'active' && !$isFullyVerified): ?>
                                    <a href="<?= base_url('documentation/' . $req->id) ?>" class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Bantu Upload Dokumentasi">
                                        <i data-lucide="file-up" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if ($isStaff && $req->status === 'draft'): ?>
                                    <div class="flex gap-1.5">
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