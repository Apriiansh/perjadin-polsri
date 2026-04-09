<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900"><?= esc($title ?? 'Perjadin Mahasiswa') ?></h1>
        <p class="text-sm font-medium text-slate-500">Kelola data perjalanan dinas mahasiswa secara efisien.</p>
    </div>

    <?php if ($isStaff ?? false): ?>
        <a href="<?= base_url('travel/student/create') ?>" class="btn-secondary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Input Perjadin Mahasiswa
        </a>
    <?php endif; ?>
</div>

<?php if (isset($stats)): ?>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="<?= base_url('travel/student?status=all') ?>" class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md hover:border-blue-200 <?= (($currentStatus ?? 'all') === 'all') ? 'ring-2 ring-blue-500 ring-offset-2' : '' ?>">
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

        <a href="<?= base_url('travel/student?status=draft') ?>" class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md hover:border-slate-300 <?= (($currentStatus ?? 'all') === 'draft') ? 'ring-2 ring-slate-400 ring-offset-2' : '' ?>">
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

        <a href="<?= base_url('travel/student?status=active') ?>" class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md hover:border-amber-200 <?= (($currentStatus ?? 'all') === 'active') ? 'ring-2 ring-amber-500 ring-offset-2' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Aktif / Berjalan</p>
                    <h3 class="mt-1 text-2xl font-bold text-amber-600"><?= number_format($stats['active'] ?? 0) ?></h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition-transform group-hover:scale-110">
                    <i data-lucide="activity" class="h-6 w-6"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-amber-500 opacity-20"></div>
        </a>

        <a href="<?= base_url('travel/student?status=completed') ?>" class="group relative overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md hover:border-emerald-200 <?= (($currentStatus ?? 'all') === 'completed') ? 'ring-2 ring-emerald-500 ring-offset-2' : '' ?>">
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

<div class="sticky top-16 z-20 flex items-center gap-1 mb-8 p-1.5 bg-white/60 backdrop-blur-xl rounded-2xl w-fit border border-slate-200/60 shadow-sm">
    <?php
    $tabs = [
        'all'       => ['label' => 'Semua', 'icon' => 'layers'],
        'draft'     => ['label' => 'Draft', 'icon' => 'file-edit'],
        'active'    => ['label' => 'Aktif', 'icon' => 'activity'],
        'completed' => ['label' => 'Selesai', 'icon' => 'check-circle-2'],
        'cancelled' => ['label' => 'Batal', 'icon' => 'x-circle'],
    ];
    foreach ($tabs as $key => $tab):
        $isActive = (($currentStatus ?? 'all') === $key);
    ?>
        <a href="<?= base_url('travel/student?status=' . $key) ?>"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold font-heading transition-all <?= $isActive ? 'bg-white text-primary-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50' ?>">
            <i data-lucide="<?= $tab['icon'] ?>" class="w-3.5 h-3.5 <?= $isActive ? 'text-primary-500' : 'text-slate-400' ?>"></i>
            <?= $tab['label'] ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card overflow-visible p-0 border-none shadow-premium bg-white">
    <div class="overflow-x-auto">
        <table id="studentTravelTable" class="w-full text-sm">
            <thead class="bg-slate-50/80 text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">No</th>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">Ketua & Anggota</th>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">Informasi Surat Tugas</th>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">Tujuan & Perihal</th>
                    <th class="px-5 py-5 text-center font-bold uppercase tracking-wider text-[10px]">Status</th>
                    <th class="px-5 py-5 text-center font-bold uppercase tracking-wider text-[10px] w-40">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <i data-lucide="layers" class="h-10 w-10 opacity-20"></i>
                                <span class="text-sm font-medium italic">Belum ada data pengajuan mahasiswa pada status ini</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($requests as $index => $req) : ?>
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-4 py-4 text-slate-500 font-medium"><?= $index + 1 ?></td>
                        <td class="px-5 py-4">
                            <?php $members = $membersByRequest[$req->id] ?? []; ?>
                            <div class="flex flex-col gap-2">
                                <?php if (!empty($members)): ?>
                                    <?php foreach ($members as $member): ?>
                                        <?php
                                        $isRepresentative = (int) ($member->is_representative ?? 0) === 1;
                                        $roleLabel = $isRepresentative ? 'Ketua' : ($member->jabatan ?? 'Anggota');
                                        ?>
                                        <div class="flex items-center justify-between gap-2 px-2 py-1.5 bg-slate-50/50 border <?= $isRepresentative ? 'border-primary-200 bg-primary-50/30' : 'border-slate-100' ?> rounded-lg w-full max-w-[280px] transition-all hover:bg-white hover:border-slate-200 hover:shadow-sm">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="flex h-7 w-7 shrink-0 <?= $isRepresentative ? 'bg-primary-500 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200' ?> rounded-lg items-center justify-center text-[10px] font-bold uppercase">
                                                    <?= esc(substr((string) ($member->name ?? 'M'), 0, 1)) ?>
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-[11px] font-bold <?= $isRepresentative ? 'text-primary-700' : 'text-slate-800' ?> truncate leading-tight">
                                                        <?= esc($member->name ?? '-') ?>
                                                    </span>
                                                    <span class="text-[9px] text-slate-400 font-medium">
                                                        <?= esc($member->nim ?? '-') ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="shrink-0 px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[9px] font-bold uppercase tracking-wide text-slate-500">
                                                <?= esc($roleLabel) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">Belum ada mahasiswa ditugaskan</span>
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
                                <span class="text-[11px] font-medium text-slate-400 line-clamp-1 italic" title="<?= esc($req->perihal) ?>">
                                    "<?= esc(mb_strimwidth($req->perihal ?? '-', 0, 50, '...')) ?>"
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <?php
                            $badgeMap = [
                                'draft'     => ['bg-slate-50 text-slate-500 border-slate-200', 'bg-slate-400', 'Draft'],
                                'active'    => ['bg-blue-50 text-blue-600 border-blue-200', 'bg-blue-500', 'Aktif'],
                                'completed' => ['bg-emerald-50 text-emerald-600 border-emerald-200', 'bg-emerald-500', 'Selesai'],
                                'cancelled' => ['bg-rose-50 text-rose-600 border-rose-200', 'bg-rose-500', 'Dibatalkan'],
                            ];
                            $badge = $badgeMap[$req->status] ?? $badgeMap['draft'];
                            ?>
                            <div class="flex justify-center">
                                <span class="inline-flex min-w-[100px] justify-center items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-extrabold uppercase tracking-widest <?= $badge[0] ?> shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full <?= $badge[1] ?> animate-pulse"></span>
                                    <?= $badge[2] ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <?php
                            $canEnrich = auth()->user()->inGroup('superadmin') && $req->status === 'draft';
                            $canVerification = ($isVerificator ?? false) && ($req->status === 'active');
                            $canDocumentation = (($isStaff ?? false) || ($isVerificator ?? false) || in_array($req->id, $representativeRequestIds ?? []))
                                && ($req->status === 'active');
                            ?>
                            <div class="flex items-center justify-center gap-1.5">
                                <?php if ($canEnrich): ?>
                                    <a href="<?= base_url('travel/student/' . $req->id . '/enrichment') ?>"
                                        class="flex h-8 items-center gap-1.5 px-3 rounded-lg bg-primary-600 text-white text-[10px] font-black uppercase tracking-wider hover:bg-primary-700 shadow-sm hover:shadow-md transition-all active:scale-95"
                                        title="Lengkapi Data">
                                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                                        <span>Lengkapi</span>
                                    </a>
                                <?php endif; ?>

                                <?php if ($canVerification): ?>
                                    <a href="<?= base_url('travel/student/' . $req->id . '/verification') ?>"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-100 transition-all active:scale-90 shadow-sm"
                                        title="Verifikasi Dokumentasi">
                                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if ($canDocumentation): ?>
                                    <a href="<?= base_url('travel/student/' . $req->id . '/documentation') ?>"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-100 transition-all active:scale-90 shadow-sm"
                                        title="Kelola Dokumentasi">
                                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="<?= base_url('travel/student/' . $req->id) ?>" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-primary-600 hover:border-primary-200 hover:bg-primary-50 transition-all active:scale-90 shadow-sm" title="Lihat Detail">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                                <?php if ($isStaff && $req->status === 'active'): ?>
                                    <button onclick="confirmCancel(<?= $req->id ?>)" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-orange-600 hover:bg-orange-50 transition-all shadow-sm" title="Batalkan">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>

                                <?php if ($isStaff && ($req->status === 'draft' || $req->status === 'cancelled')): ?>
                                    <button onclick="confirmDelete(<?= $req->id ?>)" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all shadow-sm" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
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
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Pengajuan?',
            text: "Data ini tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `<?= base_url('travel/student') ?>/${id}/destroy`;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '<?= csrf_token() ?>';
                csrf.value = '<?= csrf_hash() ?>';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    function confirmCancel(id) {
        Swal.fire({
            title: 'Batalkan Perjalanan Dinas?',
            text: "Status pengajuan akan diubah menjadi 'Dibatalkan'. Lanjutkan?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `<?= base_url('travel/student') ?>/${id}/cancel`;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '<?= csrf_token() ?>';
                csrf.value = '<?= csrf_hash() ?>';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>