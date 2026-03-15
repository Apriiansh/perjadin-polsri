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
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total -->
        <a href="<?= base_url('travel?status=all') ?>" class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md hover:border-blue-200 <?= ($currentStatus === 'all') ? 'ring-2 ring-blue-500 ring-offset-2' : '' ?>">
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
        </a>

        <!-- Draft -->
        <a href="<?= base_url('travel?status=draft') ?>" class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md hover:border-slate-300 <?= ($currentStatus === 'draft') ? 'ring-2 ring-slate-400 ring-offset-2' : '' ?>">
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
        </a>

        <!-- Pending Verification -->
        <a href="<?= base_url('travel?status=active') ?>" class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md hover:border-amber-200 <?= ($currentStatus === 'active') ? 'ring-2 ring-amber-500 ring-offset-2' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Aktif / Berjalan</p>
                    <h3 class="mt-1 text-2xl font-bold text-amber-600"><?= number_format($stats['pending'] ?? 0) ?></h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition-transform group-hover:scale-110">
                    <i data-lucide="shield-alert" class="h-6 w-6"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-amber-500 opacity-20"></div>
        </a>

        <!-- Completed -->
        <a href="<?= base_url('travel?status=completed') ?>" class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md hover:border-emerald-200 <?= ($currentStatus === 'completed') ? 'ring-2 ring-emerald-500 ring-offset-2' : '' ?>">
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
        </a>
    </div>
<?php endif; ?>

<!-- Status Tabs -->
<div class="flex items-center gap-1 mb-6 p-1 bg-slate-100/50 rounded-xl w-fit border border-slate-200/60 sticky top-0 z-10 backdrop-blur-md">
    <?php
    $tabs = [
        'all'       => ['label' => 'Semua', 'icon' => 'layers'],
        'draft'     => ['label' => 'Draft', 'icon' => 'file-edit'],
        'active'    => ['label' => 'Aktif', 'icon' => 'activity'],
        'completed' => ['label' => 'Selesai', 'icon' => 'check-circle-2'],
        'cancelled' => ['label' => 'Batal', 'icon' => 'x-circle'],
    ];
    foreach ($tabs as $key => $tab):
        $isActive = ($currentStatus === $key);
    ?>
        <a href="<?= base_url('travel?status=' . $key) ?>"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold font-heading transition-all <?= $isActive ? 'bg-white text-primary-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' ?>">
            <i data-lucide="<?= $tab['icon'] ?>" class="w-3.5 h-3.5 <?= $isActive ? 'text-primary-500' : 'text-slate-400' ?>"></i>
            <?= $tab['label'] ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card overflow-hidden p-0 border-none shadow-premium bg-white/80 backdrop-blur-md">
    <div class="overflow-x-auto">
        <table id="travelTable" class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-[10px]">No</th>
                    <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-[10px]">Pegawai & Dokumentasi</th>
                    <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-[10px]">Informasi Surat Tugas</th>
                    <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-[10px]">Tujuan & Perihal</th>
                    <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-[10px]">Status</th>
                    <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                <?php foreach ($travelRequests as $index => $req) : ?>
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-4 py-4 text-slate-500 font-medium"><?= $index + 1 ?></td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col gap-2">
                                <?php if (!empty($req->members)): ?>
                                    <?php foreach ($req->members as $member): ?>
                                        <?php $isThisMember = ($member->user_id == auth()->id()); ?>
                                        <div class="flex items-center justify-between gap-3 px-2 py-1.5 bg-slate-50/50 border <?= $isThisMember ? 'border-primary-200 bg-primary-50/30' : 'border-slate-100' ?> rounded-lg w-full max-w-[240px] transition-all hover:bg-white hover:border-slate-200 hover:shadow-sm">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="flex h-7 w-7 shrink-0 <?= $isThisMember ? 'bg-primary-500 shadow-sm' : 'bg-white text-slate-600 border border-slate-200' ?> rounded-lg items-center justify-center text-[10px] font-bold uppercase">
                                                    <?= substr($member->employee_name ?? 'P', 0, 1) ?>
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-[11px] font-bold <?= $isThisMember ? 'text-primary-700' : 'text-slate-800' ?> truncate leading-tight">
                                                        <?= esc($member->employee_name ?? '-') ?>
                                                    </span>
                                                    <span class="text-[9px] text-slate-400 font-medium"><?= esc($member->employee_nip ?? '-') ?></span>
                                                </div>
                                            </div>

                                            <?php if ($req->status === 'active' || $req->status === 'completed'): ?>
                                                <?php
                                                $isVerifAccess = auth()->user()->inGroup('superadmin', 'verificator');
                                                $docColor = 'slate';
                                                $docIcon = 'file-text';
                                                $docTitle = 'Belum ada dokumen';

                                                if ($member->uploaded_docs > 0) {
                                                    $docColor = 'amber';
                                                    $docIcon = 'file-up';
                                                    $docTitle = ($member->uploaded_docs) . ' Dokumen diunggah';
                                                }
                                                if ($member->verified_docs > 0) {
                                                    $isFull = $member->verified_docs === $member->total_docs && $member->total_docs > 0;
                                                    $docColor = $isFull ? 'emerald' : 'blue';
                                                    $docIcon = $isFull ? 'check-circle-2' : 'file-check';
                                                    $docTitle = $member->verified_docs . '/' . $member->total_docs . ' Terverifikasi';
                                                }

                                                $docUrl = $isVerifAccess
                                                    ? base_url('documentation/' . $req->id . '/verification#member-' . $member->id)
                                                    : base_url('documentation/' . $req->id);
                                                ?>

                                                <?php if ($isVerifAccess || $isThisMember): ?>
                                                    <a href="<?= $docUrl ?>"
                                                        class="flex h-6 w-6 items-center justify-center rounded-lg bg-<?= $docColor ?>-50 text-<?= $docColor ?>-600 border border-<?= $docColor ?>-100 hover:bg-white hover:shadow-sm transition-all shrink-0"
                                                        title="<?= $docTitle ?>">
                                                        <i data-lucide="<?= $docIcon ?>" class="w-3.5 h-3.5"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-50 text-slate-300 border border-slate-100 shrink-0 opacity-50" title="<?= $docTitle ?>">
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
                        <td class="px-5 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="font-extrabold text-slate-800 tracking-tight leading-tight"><?= esc($req->no_surat_tugas ?? '-') ?></span>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-4 h-4 rounded-full bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="calendar" class="w-2.5 h-2.5 text-slate-400"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                        <?= date('d M Y', strtotime($req->tgl_surat_tugas)) ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-1.5 text-slate-700 bg-primary-50 px-2 py-0.5 rounded border border-primary-100 w-fit">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-primary-500"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-tight"><?= esc($req->destination_province ?? '-') ?></span>
                                </div>
                                <span class="text-[11px] font-medium text-slate-400 line-clamp-1 italic" title="<?= esc($req->perihal_surat_rujukan) ?>">
                                    "<?= esc(mb_strimwidth($req->perihal_surat_rujukan ?? '-', 0, 50, '...')) ?>"
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <?php
                            $badgeMap = [
                                'draft'     => ['bg-slate-50 text-slate-500 border-slate-200', 'bg-slate-400', 'Draft'],
                                'active'    => ['bg-blue-50 text-blue-600 border-blue-200', 'bg-blue-500', 'Aktif'],
                                'completed' => ['bg-emerald-50 text-emerald-600 border-emerald-200', 'bg-emerald-500', 'Selesai'],
                                'cancelled' => ['bg-rose-50 text-rose-600 border-rose-200', 'bg-rose-500', 'Dibatalkan'],
                            ];
                            $badge = $badgeMap[$req->status] ?? $badgeMap['draft'];
                            $isStaffOrVerif = auth()->user()->inGroup('superadmin', 'admin', 'verificator');
                            ?>
                            <div class="flex flex-col items-center gap-2">
                                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-extrabold uppercase tracking-widest <?= $badge[0] ?> min-w-[100px] justify-center shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full <?= $badge[1] ?> animate-pulse"></span>
                                    <?= $badge[2] ?>
                                </span>

                                <?php if ($req->status === 'active'): ?>
                                    <!-- Team stats -->
                                    <div class="flex items-center gap-1.5">
                                        <?php if (isset($req->uploaded_docs) && $req->uploaded_docs > 0): ?>
                                            <div class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-600 text-[9px] font-black border border-amber-100" title="Dokumen baru menunggu verifikasi">
                                                <i data-lucide="alert-circle" class="w-2.5 h-2.5"></i>
                                                <?= $req->uploaded_docs ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($req->verified_docs) && $req->verified_docs > 0): ?>
                                            <div class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[9px] font-black border border-emerald-100" title="Dokumen terverifikasi">
                                                <i data-lucide="check-check" class="w-2.5 h-2.5"></i>
                                                <?= $req->verified_docs ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <?php
                                $isStaff = auth()->user()->inGroup('superadmin', 'admin');
                                $isVerificator = auth()->user()->inGroup('verificator');
                                $uploadedDocs = $req->uploaded_docs ?? 0;
                                ?>



                                <?php if ($req->status === 'draft' && $isStaff): ?>
                                    <a href="<?= base_url('travel/' . $req->id . '/enrichment') ?>"
                                        class="flex h-8 items-center gap-1.5 px-3 rounded-lg bg-primary-600 text-white text-[10px] font-black uppercase tracking-wider hover:bg-primary-700 shadow-sm hover:shadow-md transition-all active:scale-95">
                                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                                        <span>Lengkapi</span>
                                    </a>
                                <?php elseif ($req->status === 'active' || $req->status === 'completed'): ?>
                                    <?php if (($isStaff || $isVerificator) && ($uploadedDocs > 0 || $req->status === 'active')): ?>
                                        <a href="<?= base_url('documentation/' . $req->id . '/verification') ?>"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-100 transition-all active:scale-90 shadow-sm"
                                            title="Verifikasi Dokumentasi">
                                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($req->personal_stats)): ?>
                                        <a href="<?= base_url('documentation/' . $req->id) ?>"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-100 transition-all active:scale-90 shadow-sm"
                                            title="Kelola Dokumentasi Saya">
                                            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($isStaff && $req->status === 'draft'): ?>
                                    <div class="h-4 w-px bg-slate-100 mx-0.5"></div>
                                    <a href="<?= base_url('travel/' . $req->id . '/edit') ?>" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 shadow-sm hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Data Dasar">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <form action="<?= base_url('travel/' . $req->id . '/destroy') ?>" method="post" onsubmit="return confirm('Hapus pengajuan ini secara permanen?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 shadow-sm hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <!-- Detail -->
                                <a href="<?= base_url('travel/' . $req->id) ?>"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-primary-600 hover:border-primary-200 hover:bg-primary-50 transition-all active:scale-90 shadow-sm"
                                    title="Lihat Detail">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
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