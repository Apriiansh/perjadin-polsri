<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
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

<?= $this->section('content') ?>

<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="<?= base_url('travel/student') ?>" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Detail Perjadin Mahasiswa</span>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-0.5 text-sm text-slate-500">Nomor Surat Tugas: <span class="font-semibold text-slate-700"><?= esc($request->no_surat_tugas ?? 'Belum tersedia') ?></span></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left Column: Information -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Informasi Dokumen & Surat Rujukan -->
        <div class="card p-6 border-t-4 border-t-primary-500">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-50 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Informasi Dokumen</h3>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-tight">Detail Surat Tugas & Rujukan</p>
                    </div>
                </div>

                <?php
                $statusColors = [
                    'draft'     => 'bg-slate-200 text-slate-700',
                    'active'    => 'bg-emerald-200 text-emerald-700',
                    'completed' => 'bg-blue-200 text-blue-700',
                    'cancelled' => 'bg-orange-200 text-orange-700',
                ];
                $statusLabels = [
                    'draft'     => 'Draft',
                    'active'    => 'Aktif',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                ];
                $statusClass = $statusColors[$request->status] ?? $statusColors['draft'];
                $statusLabel = $statusLabels[$request->status] ?? $request->status;
                ?>
                <span class="px-3 py-1.5 text-xs font-bold rounded-lg shadow-sm uppercase tracking-wider <?= $statusClass ?> border border-current opacity-80">
                    <?= esc($statusLabel) ?>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Group 1: Surat Tugas -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-y-4">
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Nomor Surat Tugas</span>
                            <span class="text-sm font-bold text-slate-900"><?= esc($request->no_surat_tugas ?: '-') ?></span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Tgl Surat Tugas</span>
                                <span class="text-sm font-medium text-slate-800"><?= esc($request->tgl_surat_tugas ? date('d F Y', strtotime($request->tgl_surat_tugas)) : '-') ?></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Tahun</span>
                                <span class="text-sm font-medium text-slate-800"><?= esc($request->tahun_anggaran ?: '-') ?></span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Beban Anggaran / MAK</span>
                            <div class="space-y-1">
                                <span class="text-sm font-medium text-slate-800 block"><?= esc($request->budget_burden_by ?: '-') ?></span>
                                <?php if (!empty($request->mak)): ?>
                                    <span class="text-[11px] font-mono font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded inline-block border border-slate-200">
                                        MAK: <?= esc($request->mak) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Venue / Keterangan Lokasi</span>
                            <div class="flex items-center gap-2 text-sm font-medium text-slate-800">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                <?= esc($request->lokasi ?: 'Belum dicantumkan') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Surat Rujukan -->
                <div class="space-y-4 md:border-l md:border-slate-100 md:pl-8">
                    <?php if (!empty($request->nomor_surat_rujukan) || !empty($request->instansi_pengirim_rujukan) || !empty($request->perihal)): ?>
                        <div class="grid grid-cols-1 gap-y-4">
                            <?php if (!empty($request->nomor_surat_rujukan)): ?>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Surat Rujukan</span>
                                    <div class="text-sm font-bold text-slate-900"><?= esc($request->nomor_surat_rujukan) ?></div>
                                    <?php if (!empty($request->tgl_surat_rujukan)): ?>
                                        <div class="text-xs text-slate-500 mt-0.5"><?= date('d F Y', strtotime($request->tgl_surat_rujukan)) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($request->instansi_pengirim_rujukan)): ?>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Instansi Pengirim</span>
                                    <span class="text-sm font-medium text-slate-800"><?= esc($request->instansi_pengirim_rujukan) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($request->perihal)): ?>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Perihal</span>
                                    <p class="text-xs text-slate-600 italic leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100 mt-1">
                                        <?= nl2br(esc((string) $request->perihal)) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="h-full flex flex-col items-center justify-center text-center opacity-40 py-8">
                            <i data-lucide="mail-search" class="w-10 h-10 text-slate-300 mb-2"></i>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tidak ada surat rujukan</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tujuan & Jadwal -->
        <div class="card p-6 border-t-4 border-t-blue-500">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 mb-4 pb-4 border-b border-slate-100">
                <i data-lucide="map-pin" class="w-5 h-5 text-blue-500"></i>
                Tujuan & Jadwal Kegiatan
                <span class="text-[11px] font-bold text-blue-600 bg-blue-50/80 px-3 py-1 rounded-full border border-blue-100 shadow-sm whitespace-nowrap ml-auto">
                    <i data-lucide="calendar-clock" class="w-3.5 h-3.5 inline mr-1 opacity-70"></i>
                    <?= esc($request->duration_days) ?> Hari
                </span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tempat Berangkat</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($request->departure_place ?: '-') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tempat Tujuan</span>
                    <span class="text-sm font-medium text-slate-900">
                        <?= esc(ucwords(strtolower($request->destination_province))) ?> – <?= esc(ucwords(strtolower($request->destination_city ?: '-'))) ?>
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Berangkat</span>
                    <span class="text-sm font-medium text-slate-900"><?= !empty($request->departure_date) ? date('d F Y', strtotime($request->departure_date)) : '-' ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Kembali</span>
                    <span class="text-sm font-medium text-slate-900"><?= !empty($request->return_date) ? date('d F Y', strtotime($request->return_date)) : '-' ?></span>
                </div>
            </div>
        </div>

    </div><!-- /Left Column -->

    <!-- Sidebar Column -->
    <div class="space-y-6">

        <!-- Unduh Dokumen -->
        <div class="card p-6 border-t-4 border-t-indigo-500 shadow-md">
            <h3 class="font-bold text-sm uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-100 flex justify-between items-center">
                <span>Unduh Dokumen</span>
                <i data-lucide="download" class="w-4 h-4 text-slate-400"></i>
            </h3>

            <div class="space-y-5">
                <!-- Lampiran Surat Tugas -->
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Lampiran Surat Tugas</p>
                    <?php if (!empty($request->lampiran_path)): ?>
                        <a href="<?= base_url('travel/download/lampiran/' . $request->id) ?>"
                            class="btn-primary w-full justify-center inline-flex items-center gap-2 text-sm">
                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                            Unduh Lampiran ST
                        </a>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 italic bg-slate-50 p-3 rounded-lg border border-dashed border-slate-200 text-center">
                            Lampiran surat tugas belum tersedia.
                        </p>
                    <?php endif; ?>
                </div>

                <hr class="border-slate-100">

                <!-- Dokumen Terbitan Sistem -->
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Dokumen Terbitan Sistem</p>
                    <?php if ($request->status !== 'draft'): ?>
                        <a href="<?= base_url('travel/student/' . $request->id . '/download') ?>"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-400 px-4 py-2.5 text-xs font-bold text-white shadow-md hover:bg-indigo-700 transition-all">
                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                            SPJ Perjadin Mahasiswa (.xlsx)
                        </a>
                    <?php else: ?>
                        <div class="bg-indigo-50/50 border border-dashed border-indigo-200 rounded-lg p-4 text-center">
                            <div class="w-10 h-10 rounded-full bg-white mx-auto mb-3 flex items-center justify-center shadow-sm">
                                <i data-lucide="lock" class="w-5 h-5 text-indigo-400"></i>
                            </div>
                            <p class="text-[11px] font-medium text-indigo-600 leading-relaxed px-2">
                                Dokumen otomatis terlampir setelah data dilengkapi Keuangan.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tindakan Administratif -->
        <div class="card p-6 border-t-4 border-t-amber-500 bg-amber-50/30 shadow-md">
            <h3 class="font-bold text-xs uppercase tracking-wider text-amber-800 mb-4 pb-2 border-b border-amber-200/50 flex justify-between items-center">
                <span>Tindakan Administratif</span>
                <i data-lucide="shield" class="w-4 h-4 text-amber-500"></i>
            </h3>

            <div class="flex flex-col gap-3">
                <!-- Status for Student in Draft state -->
                <?php if (!$isStaff && !auth()->user()->inGroup('superadmin', 'verificator') && $request->status === 'draft'): ?>
                    <div class="p-4 rounded-xl bg-amber-100/50 border border-amber-200 text-amber-800">
                        <div class="flex items-center gap-2 mb-1.5">
                            <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                            <span class="text-xs font-bold uppercase tracking-wider">Status: Draft</span>
                        </div>
                        <p class="text-[11px] font-medium leading-relaxed italic">
                            Menunggu pihak kampus melengkapi data perjalanan dinas.
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Administrative actions for Staff in Draft state -->
                <?php if ($isStaff && $request->status === 'draft'): ?>
                    <p class="text-[10px] text-slate-500 bg-slate-50 p-2 rounded-lg leading-relaxed border border-slate-200 italic">
                        Data dasar pengajuan mahasiswa dikelola pada form input. Untuk tahap berikutnya, lanjutkan ke menu <strong>Lengkapi Data</strong>.
                    </p>
                <?php endif; ?>

                <!-- Administrative actions for Keuangan (Superadmin) in Draft/Active state -->
                <?php if (auth()->user()->inGroup('superadmin') && ($request->status === 'draft' || $request->status === 'active')): ?>
                    <a href="<?= base_url('travel/student/' . $request->id . '/enrichment') ?>" class="btn-primary w-full justify-center shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        <?= $request->status === 'draft' ? 'Lengkapi Data' : 'Revisi Kelengkapan Data' ?>
                    </a>
                <?php endif; ?>

                <!-- Actions for Leader in Active/Completed state -->
                <?php if ($isLeader && ($request->status === 'active' || $request->status === 'completed')): ?>
                    <?php $isDone = $request->status === 'completed'; ?>
                    <a href="<?= base_url('travel/student/' . $request->id . '/documentation') ?>" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg <?= $isDone ? 'bg-slate-600' : 'bg-indigo-600' ?> px-5 text-xs font-bold text-white shadow-md hover:opacity-90 transition-all">
                        <i data-lucide="<?= $isDone ? 'eye' : 'upload-cloud' ?>" class="w-3.5 h-3.5"></i>
                        <?= $isDone ? 'Lihat Dokumentasi' : 'Dokumentasi Tim' ?>
                    </a>
                    <?php if ($hasPendingValidationDocs ?? false): ?>
                        <div class="text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            Status dokumentasi: <strong>Menunggu validasi</strong>.
                        </div>
                    <?php elseif ($hasRejectedDocs ?? false): ?>
                        <div class="text-[10px] font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2">
                            Status dokumentasi: <strong>Perlu perbaikan</strong>.
                        </div>
                    <?php elseif (($hasVerifiedDocs ?? false) && $request->status !== 'draft'): ?>
                        <div class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                            Status dokumentasi: <strong>Sudah tervalidasi</strong>.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Actions for Verificator/Superadmin in Active/Completed state -->
                <?php if (auth()->user()->inGroup('verificator', 'superadmin') && ($request->status === 'active' || $request->status === 'completed') && ($hasDocumentation ?? false)): ?>
                    <?php $isDone = $request->status === 'completed'; ?>
                    <a href="<?= base_url('travel/student/' . $request->id . '/verification') ?>" class="<?= $isDone ? 'bg-emerald-600 hover:bg-emerald-700' : 'btn-success' ?> w-full justify-center shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2 text-white h-10 rounded-lg text-xs font-bold">
                        <i data-lucide="<?= $isDone ? 'clipboard-list' : 'shield-check' ?>" class="w-3.5 h-3.5"></i>
                        <?= $isDone ? 'Review Hasil Verifikasi' : 'Verifikasi Bukti' ?>
                    </a>
                <?php endif; ?>

                <?php if ($isStaff && $request->status === 'active'): ?>
                    <button type="button" onclick="confirmCancel(<?= $request->id ?>)" class="w-full flex h-10 items-center justify-center gap-2 rounded-lg bg-orange-50 border border-orange-200 text-orange-600 hover:bg-orange-100 transition-all shadow-sm text-xs font-bold">
                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        Batalkan Perjadin
                    </button>
                <?php endif; ?>

                <?php if ($isStaff && ($request->status === 'draft' || $request->status === 'cancelled')): ?>
                    <button type="button" onclick="confirmDelete(<?= $request->id ?>)" class="w-full flex h-10 items-center justify-center gap-2 rounded-lg bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 transition-all shadow-sm text-xs font-bold">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        Hapus
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Penandatangan -->
        <div class="card p-6 border-t-4 border-t-slate-800 shadow-md">
            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-100 flex justify-between items-center">
                <span>Penandatangan</span>
                <i data-lucide="signature" class="w-3.5 h-3.5 text-slate-300"></i>
            </h3>
            <div class="space-y-6">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pejabat Pembuat Komitmen</p>
                    <p class="text-sm font-bold text-slate-800 leading-tight"><?= esc($request->ppk_name ?? '-') ?></p>
                    <p class="text-[11px] text-primary-600 font-semibold tabular-nums">NIP. <?= esc($request->ppk_nip ?? '-') ?></p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Bendahara Pengeluaran</p>
                    <p class="text-sm font-bold text-slate-800 leading-tight"><?= esc($request->bendahara_name ?? '-') ?></p>
                    <p class="text-[11px] text-primary-600 font-semibold tabular-nums">NIP. <?= esc($request->bendahara_nip ?? '-') ?></p>
                </div>
            </div>
        </div>

    </div><!-- /Sidebar -->
</div>

<!-- Bottom Section: Member & Expenses -->
<div class="mt-6">
    <div class="card p-0 border-t-4 border-t-emerald-500 shadow-md bg-white overflow-hidden">
        <div class="p-6 pb-0 bg-slate-50/30">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 shadow-sm">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Daftar Mahasiswa & Rincian Biaya</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Rangkuman Penerimaan Per Anggota Tim</p>
                    </div>
                </div>
                <div class="px-5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 text-center leading-none mb-1">Total Peserta</p>
                    <p class="text-xl font-bold text-slate-800 leading-none text-center tabular-nums"><?= count($members) ?></p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Data Mahasiswa</th>
                        <th class="px-4 py-4 font-semibold text-right">Uang Saku</th>
                        <th class="px-4 py-4 font-semibold text-right">Transport</th>
                        <th class="px-4 py-4 font-semibold text-right">Tiket</th>
                        <th class="px-4 py-4 font-semibold text-right">Akomodasi</th>
                        <th class="px-4 py-4 font-semibold text-right">Lain-lain</th>
                        <th class="px-6 py-4 font-semibold text-right text-emerald-700 bg-emerald-50/30">Total Terima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php
                    $grandTotal = 0;
                    foreach ($members as $m):
                        $grandTotal += $m->total_amount;
                        $expensesByCat = [];
                        foreach ($m->expenses as $e) {
                            $expensesByCat[$e->category][] = [
                                'amount' => $e->amount,
                                'description' => $e->item_name
                            ];
                        }
                    ?>
                        <tr class="hover:bg-emerald-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col min-w-40">
                                    <span class="font-bold text-slate-800"><?= esc($m->name) ?></span>
                                    <span class="text-[10px] font-mono text-slate-500 leading-tight mt-0.5"><?= esc($m->nim) ?></span>
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <div class="w-1 h-1 rounded-full bg-primary-500"></div>
                                        <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400"><?= esc($m->jabatan) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-700 tabular-nums border-r border-slate-50">
                                <?php if (!empty($expensesByCat['pocket_money'])): ?>
                                    <?php foreach ($expensesByCat['pocket_money'] as $item): ?>
                                        <div class="mb-2 last:mb-0">
                                            <div class="font-bold text-slate-900"><?= number_format($item['amount'], 0, ',', '.') ?></div>
                                            <div class="text-[9px] text-slate-400 font-medium leading-none"><?= esc($item['description'] ?: 'Uang Saku') ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-700 tabular-nums border-r border-slate-50">
                                <?php if (!empty($expensesByCat['transport'])): ?>
                                    <?php foreach ($expensesByCat['transport'] as $item): ?>
                                        <div class="mb-2 last:mb-0">
                                            <div class="font-bold text-slate-900"><?= number_format($item['amount'], 0, ',', '.') ?></div>
                                            <div class="text-[9px] text-slate-400 font-medium leading-none"><?= esc($item['description'] ?: 'Transport') ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-700 tabular-nums border-r border-slate-50">
                                <?php if (!empty($expensesByCat['ticket'])): ?>
                                    <?php foreach ($expensesByCat['ticket'] as $item): ?>
                                        <div class="mb-2 last:mb-0">
                                            <div class="font-bold text-slate-900"><?= number_format($item['amount'], 0, ',', '.') ?></div>
                                            <div class="text-[9px] text-slate-400 font-medium leading-none"><?= esc($item['description'] ?: 'Tiket') ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-700 tabular-nums border-r border-slate-50">
                                <?php if (!empty($expensesByCat['accommodation'])): ?>
                                    <?php foreach ($expensesByCat['accommodation'] as $item): ?>
                                        <div class="mb-2 last:mb-0">
                                            <div class="font-bold text-slate-900"><?= number_format($item['amount'], 0, ',', '.') ?></div>
                                            <div class="text-[9px] text-slate-400 font-medium leading-none"><?= esc($item['description'] ?: 'Akomodasi') ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-700 tabular-nums border-r border-slate-50">
                                <?php if (!empty($expensesByCat['other'])): ?>
                                    <?php foreach ($expensesByCat['other'] as $item): ?>
                                        <div class="mb-2 last:mb-0">
                                            <div class="font-bold text-slate-900"><?= number_format($item['amount'], 0, ',', '.') ?></div>
                                            <div class="text-[9px] text-slate-400 font-medium leading-none"><?= esc($item['description'] ?: 'Lain-lain') ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right bg-emerald-50/10 font-bold text-emerald-700 tabular-nums">
                                <?= number_format($m->total_amount, 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-slate-800 border-t border-slate-700">
                    <tr>
                        <td colspan="6" class="px-6 py-5 text-right font-bold text-slate-200 text-sm tracking-wide uppercase">Grand Total Anggaran</td>
                        <td class="px-6 py-5 text-right font-black text-xl text-emerald-400 whitespace-nowrap">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- System Footer Info -->
<div class="text-[10px] text-center text-slate-400 space-y-1 mt-10 pb-10 uppercase tracking-widest">
    <p>Data Monitoring System</p>
    <p>Dibuat: <?= date('d M Y H:i', strtotime($request->created_at)) ?></p>
    <?php if ($request->updated_at && $request->updated_at !== $request->created_at): ?>
        <p>Terakhir Diubah: <?= date('d M Y H:i', strtotime($request->updated_at)) ?></p>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>