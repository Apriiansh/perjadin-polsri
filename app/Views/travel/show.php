<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="<?= base_url('travel') ?>" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Detail Perjalanan Dinas</span>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-0.5 text-sm text-slate-500">Nomor Surat Tugas: <span class="font-semibold text-slate-700"><?= esc($travelRequest->no_surat_tugas ?? 'Belum ada nomor') ?></span></p>
    </div>

    <?php
    $user = auth()->user();
    $isDosen = $user->inGroup('lecturer');
    $isAdminKepegawaian = $user->inGroup('admin');
    $isKeuangan = $user->inGroup('superadmin', 'verificator');
    $isStaff = $user->inGroup('admin', 'superadmin');

    // Find my member ID if lecturer/member to ensure individual download only
    $myMemberId = null;
    if ($isDosen) {
        $currentEmp = model('App\Models\EmployeeModel')->where('user_id', auth()->id())->first();
        if ($currentEmp) {
            foreach ($members as $m) {
                // Check if $m is array or object
                $mId = is_array($m) ? $m['employee_id'] : $m->employee_id;
                $empId = is_array($currentEmp) ? $currentEmp['id'] : $currentEmp->id;

                if ($mId == $empId) {
                    $myMemberId = is_array($m) ? $m['travel_member_id'] : $m->travel_member_id;
                    break;
                }
            }
        }
    }
    ?>

    <div class="flex items-center gap-2">
        <?php if (($isStaff ?? false) && $travelRequest->status === 'draft'): ?>
            <a href="<?= base_url('travel/' . $travelRequest->id . '/edit') ?>" class="btn-primary inline-flex items-center gap-2 text-sm">
                <i data-lucide="edit" class="w-4 h-4"></i>
                Edit
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- =========================================================
     MAIN 2-COL GRID: Info (left) + Actions (right)
     ========================================================= -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left Column -->
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
                $statusClass = $statusColors[$travelRequest->status] ?? $statusColors['draft'];
                $statusLabel = $statusLabels[$travelRequest->status] ?? $travelRequest->status;
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
                            <span class="text-sm font-bold text-slate-900"><?= esc($travelRequest->no_surat_tugas ?: '-') ?></span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Tgl Surat Tugas</span>
                                <span class="text-sm font-medium text-slate-800"><?= esc($travelRequest->tgl_surat_tugas ? date('d F Y', strtotime($travelRequest->tgl_surat_tugas)) : '-') ?></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Tahun</span>
                                <span class="text-sm font-medium text-slate-800"><?= esc($travelRequest->tahun_anggaran ?: '-') ?></span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Beban Anggaran / MAK</span>
                            <div class="space-y-1">
                                <span class="text-sm font-medium text-slate-800 block"><?= esc($travelRequest->budget_burden_by ?: '-') ?></span>
                                <?php if (!empty($travelRequest->mak)): ?>
                                    <span class="text-[11px] font-mono font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded inline-block border border-slate-200">
                                        MAK: <?= esc($travelRequest->mak) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Lokasi / Venue</span>
                            <div class="flex items-center gap-2 text-sm font-medium text-slate-800">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                <?= esc($travelRequest->lokasi ?: '-') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Surat Rujukan -->
                <div class="space-y-4 md:border-l md:border-slate-100 md:pl-8">
                    <?php if (!empty($travelRequest->nomor_surat_rujukan) || !empty($travelRequest->instansi_pengirim_rujukan) || !empty($travelRequest->perihal)): ?>
                        <div class="grid grid-cols-1 gap-y-4">
                            <?php if (!empty($travelRequest->nomor_surat_rujukan)): ?>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Surat Rujukan</span>
                                    <div class="text-sm font-bold text-slate-900"><?= esc($travelRequest->nomor_surat_rujukan) ?></div>
                                    <?php if (!empty($travelRequest->tgl_surat_rujukan)): ?>
                                        <div class="text-xs text-slate-500 mt-0.5"><?= date('d F Y', strtotime($travelRequest->tgl_surat_rujukan)) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($travelRequest->instansi_pengirim_rujukan)): ?>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Instansi Pengirim</span>
                                    <span class="text-sm font-medium text-slate-800"><?= esc($travelRequest->instansi_pengirim_rujukan) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($travelRequest->perihal)): ?>
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Perihal</span>
                                    <p class="text-xs text-slate-600 italic leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100 mt-1">
                                        <?= nl2br(esc((string) $travelRequest->perihal)) ?>
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
                    <?= esc($travelRequest->duration_days) ?> Hari
                </span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tempat Berangkat</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->departure_place ?: '-') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tempat Tujuan</span>
                    <span class="text-sm font-medium text-slate-900">
                        <?= esc(ucwords(strtolower($travelRequest->destination_province))) ?> – <?= esc(ucwords(strtolower($travelRequest->destination_city ?: '-'))) ?>
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Berangkat</span>
                    <span class="text-sm font-medium text-slate-900"><?= !empty($travelRequest->departure_date) ? date('d F Y', strtotime($travelRequest->departure_date)) : '-' ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Kembali</span>
                    <span class="text-sm font-medium text-slate-900"><?= !empty($travelRequest->return_date) ? date('d F Y', strtotime($travelRequest->return_date)) : '-' ?></span>
                </div>
            </div>
        </div>

    </div><!-- /Left Column -->

    <!-- =====================================================
         Right Column: Unduh + Tindakan (no Laporan here)
         ===================================================== -->
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
                    <?php if (!empty($travelRequest->lampiran_path)): ?>
                        <a href="<?= base_url('travel/download/lampiran/' . $travelRequest->id) ?>"
                            class="btn-primary w-full justify-center inline-flex items-center gap-2 text-sm">
                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                            <?= esc($travelRequest->lampiran_original_name ?: 'Download Lampiran') ?>
                        </a>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 italic bg-slate-50 p-3 rounded-lg border border-dashed border-slate-200 text-center">
                            Belum ada lampiran yang diunggah.
                        </p>
                    <?php endif; ?>
                </div>

                <hr class="border-slate-100">

                <!-- Dokumen Terbitan Sistem -->
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Dokumen Terbitan Sistem</p>

                    <?php if ($travelRequest->status !== 'draft'): ?>
                        <div class="flex flex-col gap-2">
                            <div class="grid grid-cols-2 gap-2">
                                <!-- SPD Box -->
                                <?php
                                $spdUrl = base_url('travel/download/spd/' . $travelRequest->id) . '?format=pdf';
                                if (!$isStaff && !$isKeuangan && $myMemberId) {
                                    $spdUrl .= '&member_id=' . $myMemberId;
                                }
                                ?>
                                <a href="<?= $spdUrl ?>"
                                    class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white hover:border-primary-400 hover:text-primary-700 transition-all group gap-1 text-center">
                                    <div class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center group-hover:bg-primary-50">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-primary-600"></i>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] font-bold leading-none"><?= ($isStaff || $isKeuangan) ? 'SPD' : 'SPD Saya' ?></span>
                                        <span class="text-[9px] text-slate-400 leading-none">.pdf</span>
                                    </div>
                                </a>

                                <!-- Pernyataan Box -->
                                <?php
                                $stmtUrl = base_url('travel/' . $travelRequest->id . '/statement') . '?format=pdf';
                                if ($myMemberId) {
                                    $stmtUrl .= '&member_id=' . $myMemberId;
                                }
                                ?>
                                <a href="<?= $stmtUrl ?>"
                                    class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white hover:border-emerald-400 hover:text-emerald-700 transition-all group gap-1 text-center">
                                    <div class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center group-hover:bg-emerald-50">
                                        <i data-lucide="file-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] font-bold leading-none">Surat Pernyataan<?= $isStaff ? '' : ' Saya' ?></span>
                                        <span class="text-[9px] text-slate-400 leading-none">.pdf</span>
                                    </div>
                                </a>

                            </div>

                            <?php if ($isStaff && !$isAdminKepegawaian): ?>
                                <!-- Group 2: Daftar Kontrol + Daftar Nominatif (Admin only) -->
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <a href="<?= base_url('travel/' . $travelRequest->id . '/control-list') ?>"
                                        class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white hover:border-emerald-400 hover:text-emerald-700 transition-all group gap-1 text-center">
                                        <div class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center group-hover:bg-emerald-50">
                                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <span class="text-[10px] font-bold leading-none">Daftar Kontrol</span>
                                            <span class="text-[9px] text-slate-400 leading-none">.xlsx</span>
                                        </div>
                                    </a>

                                    <a href="<?= base_url('travel/' . $travelRequest->id . '/nominative-list') ?>"
                                        class="flex flex-col items-center justify-center p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white hover:border-blue-400 hover:text-blue-700 transition-all group gap-1 text-center">
                                        <div class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center group-hover:bg-blue-50">
                                            <i data-lucide="table" class="w-3.5 h-3.5 text-blue-600"></i>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <span class="text-[10px] font-bold leading-none">Daftar Nominatif</span>
                                            <span class="text-[9px] text-slate-400 leading-none">.xlsx</span>
                                        </div>
                                    </a>
                                </div>

                                <hr class="border-slate-100 my-2">

                                <!-- Row 3: Bundle Excel full width -->
                                <a href="<?= base_url('travel/' . $travelRequest->id . '/bundle-excel') ?>"
                                    class="flex items-center justify-between p-3 rounded-lg border border-amber-200 bg-amber-50/40 hover:bg-white hover:border-amber-400 transition-all group gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-md bg-white shadow-sm flex items-center justify-center shrink-0 group-hover:bg-amber-50">
                                            <i data-lucide="package" class="w-4 h-4 text-amber-500"></i>
                                        </div>
                                        <div class="min-w-0 flex flex-col">
                                            <span class="text-[10px] font-bold text-slate-700 leading-none">Seluruh Dokumen (Excel)</span>
                                            <span class="text-[9px] text-slate-400 leading-none mt-0.5">Format Excel untuk Pengarsipan</span>
                                        </div>
                                    </div>
                                    <i data-lucide="download" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                                </a>

                                <!-- Row 4: Bundle SPJ (ZIP) full width -->
                                <a href="<?= base_url('travel/' . $travelRequest->id . '/bundle-spj') ?>"
                                    class="flex items-center justify-between p-3 rounded-lg border border-indigo-200 bg-indigo-50/40 hover:bg-white hover:border-indigo-400 transition-all group gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-md bg-white shadow-sm flex items-center justify-center shrink-0 group-hover:bg-indigo-50">
                                            <i data-lucide="archive" class="w-4 h-4 text-indigo-500"></i>
                                        </div>
                                        <div class="min-w-0 flex flex-col">
                                            <span class="text-[10px] font-bold text-slate-700 leading-none">Bundle SPJ (ZIP)</span>
                                            <span class="text-[9px] text-slate-400 leading-none mt-0.5">PDF Laporan + Dokumentasi Lengkap</span>
                                        </div>
                                    </div>
                                    <i data-lucide="download" class="w-3.5 h-3.5 text-indigo-400 shrink-0"></i>
                                </a>
                            <?php endif; ?>

                        </div>
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
        <?php if ($isStaff ?? false): ?>
            <div class="card p-6 border-t-4 border-t-amber-500 bg-amber-50/30 shadow-md">
                <h3 class="font-bold text-xs uppercase tracking-wider text-amber-800 mb-4 pb-2 border-b border-amber-200/50">Tindakan Administratif</h3>

                <div class="flex flex-col gap-3">
                    <?php if ($travelRequest->status === 'draft'): ?>
                        <?php if (auth()->user()->inGroup('superadmin')): ?>
                            <a href="<?= base_url('travel/' . $travelRequest->id . '/enrichment') ?>" class="btn-primary w-full justify-center shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2">
                                <i data-lucide="clipboard-check" class="w-4 h-4"></i> Lengkapi Data
                            </a>
                            <p class="text-[10px] text-primary-600 bg-primary-50 p-2 rounded-lg leading-relaxed border border-primary-100">
                                <strong>Status Draft:</strong> Menunggu pengisian rincian biaya riil dan penandatangan oleh bagian Keuangan.
                            </p>
                        <?php else: ?>
                            <p class="text-[10px] text-slate-500 bg-slate-50 p-2 rounded-lg leading-relaxed border border-slate-200 italic">
                                <strong>Status Draft:</strong> Pengajuan Anda sedang menunggu verifikasi dan pengisian rincian biaya oleh bagian Keuangan.
                            </p>
                        <?php endif; ?>
                        <form action="<?= base_url('travel/' . $travelRequest->id . '/destroy') ?>" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-danger w-full justify-center shadow-md hover:shadow-lg transition-all" onclick="return confirm('Apakah Anda yakin ingin menghapus permintaan dinas ini secara permanen?')">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Hapus
                            </button>
                        </form>

                    <?php elseif ($travelRequest->status === 'active'): ?>
                        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100 text-[10px] text-emerald-700 leading-relaxed relative overflow-hidden group mb-1">
                            <div class="absolute -right-2 -top-2 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i data-lucide="check-circle-2" class="w-12 h-12"></i>
                            </div>
                            <strong>Status Aktif:</strong> Data sudah lengkap. Berkas siap diunduh.
                        </div>
                        <?php if ($travelRequest->status === 'active' || (auth()->user()->inGroup('superadmin') && $travelRequest->status === 'completed')): ?>
                            <div class="flex flex-col gap-2">
                                <a href="<?= base_url('travel/' . $travelRequest->id . '/enrichment') ?>" class="btn-warning w-full justify-center shadow-md hover:shadow-lg transition-all animate-in fade-in slide-in-from-bottom-2">
                                    <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> Edit Kelengkapan
                                </a>
                                <div class="flex gap-2">
                                    <?php if (auth()->user()->inGroup('superadmin') && $travelRequest->status === 'active'): ?>
                                        <button type="button" onclick="completeTravel(<?= $travelRequest->id ?>)" class="btn-success flex-1 justify-center shadow-md hover:shadow-lg transition-all animate-in fade-in slide-in-from-bottom-2">
                                            <i data-lucide="check-circle-2" class="w-4 h-4 mr-2"></i> Tandai Selesai
                                        </button>
                                    <?php endif; ?>
                                    <?php if (auth()->user()->inGroup('admin', 'superadmin')): ?>
                                        <button type="button" onclick="cancelTravel(<?= $travelRequest->id ?>)" class="btn-danger px-4 justify-center shadow-sm hover:shadow-md transition-all animate-in fade-in slide-in-from-bottom-2">
                                            <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Batalkan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($travelRequest->status === 'completed'): ?>
                        <p class="text-[10px] text-blue-600 bg-blue-50 p-2 rounded-lg leading-relaxed border border-blue-100 italic">
                            <strong>Status Selesai:</strong> Seluruh proses perjalanan dinas telah selesai dan diverifikasi.
                        </p>

                    <?php elseif ($travelRequest->status === 'cancelled'): ?>
                        <p class="text-[10px] text-red-600 bg-red-50 p-2 rounded-lg leading-relaxed border border-red-100 italic">
                            <strong>Status Dibatalkan:</strong> Pengajuan perjalanan dinas ini telah dibatalkan.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div><!-- /Right Column -->
</div>

<!-- =========================================================
     LAPORAN & DOKUMENTASI — Full Width (moved from sidebar)
     ========================================================= -->
<?php
$hasAnyDoc = false;
foreach ($members as $m) {
    if (!empty($m->report_narrative) || !empty($m->documentation_files)) {
        $hasAnyDoc = true;
        break;
    }
}
if (($hasAnyDoc || $travelRequest->status === 'active') && !empty($members)):
?>
    <div class="mt-6">
        <div class="card p-0 overflow-hidden border-t-4 border-t-blue-600 bg-white shadow-lg">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                <h3 class="font-bold text-slate-800 flex items-center gap-2 uppercase tracking-wider text-xs">
                    <i data-lucide="file-text" class="w-4 h-4 text-blue-600"></i>
                    Laporan & Dokumentasi
                </h3>
                <?php if ($travelRequest->status === 'active' || (auth()->user()->inGroup('superadmin') && $travelRequest->status === 'completed')): ?>
                    <?php
                    $isVerifOnly = auth()->user()->inGroup('verificator') && !auth()->user()->inGroup('superadmin', 'admin');
                    $targetUrl = $isVerifOnly
                        ? base_url('documentation/' . $travelRequest->id . '/verification')
                        : base_url('documentation/' . $travelRequest->id);
                    ?>
                    <a href="<?= $targetUrl ?>"
                        class="text-[10px] font-bold bg-blue-600 text-white px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 hover:bg-blue-700 transition-all shadow-sm hover:shadow-md">
                        <i data-lucide="upload-cloud" class="w-3 h-3"></i>
                        <?= $isVerifOnly ? 'Verifikasi' : 'Kelola' ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Member grid: 2 cols on larger screens -->
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                <?php foreach ($members as $member): ?>
                    <?php if ($isDosen && $member->user_id != auth()->id()) continue; ?>
                    <div class="p-5 bg-white">
                        <!-- Member header -->
                        <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100 shrink-0">
                                <?= strtoupper(substr($member->employee_name, 0, 2)) ?>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm truncate"><?= esc($member->employee_name) ?></h4>
                                <p class="text-[9px] text-slate-400 font-medium tracking-tight truncate"><?= esc($member->employee_nip) ?></p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <!-- Narrative -->
                            <?php if (!empty($member->report_narrative)): ?>
                                <div class="text-[11px] text-slate-600 leading-relaxed bg-slate-50/80 p-3 rounded-lg border border-slate-100 italic">
                                    <?= nl2br(esc((string) $member->report_narrative)) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Files grouped by item -->
                            <?php if (!empty($member->documentation_files)): ?>
                                <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                                    <?php
                                    $groupedFiles = [];
                                    foreach ($member->documentation_files as $f) {
                                        $groupedFiles[$f->item_name][] = $f;
                                    }
                                    foreach ($groupedFiles as $itemName => $files): ?>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 mb-1.5 px-0.5">
                                                <div class="w-0.5 h-2.5 rounded-full bg-blue-500 opacity-60"></div>
                                                <span class="text-[9px] font-extrabold text-slate-500 uppercase tracking-tighter truncate" title="<?= esc($itemName) ?>"><?= esc($itemName) ?></span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                <?php foreach ($files as $index => $file): ?>
                                                    <a href="<?= base_url('travel/download/file/' . $file->id) ?>"
                                                        target="_blank"
                                                        title="Buka <?= esc($file->item_name) ?> #<?= ($index + 1) ?>"
                                                        class="<?= (count($files) == 1) ? 'col-span-2' : '' ?> flex items-center justify-center p-1.5 rounded bg-slate-50 border border-slate-100 hover:border-blue-300 hover:bg-white transition-all group/file shadow-sm">
                                                        <div class="flex items-center gap-1.5">
                                                            <i data-lucide="file-text" class="w-3 h-3 text-slate-300 group-hover/file:text-blue-500"></i>
                                                            <span class="text-[8px] font-black text-slate-400 group-hover/file:text-blue-600">#<?= ($index + 1) ?></span>
                                                        </div>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (empty($member->report_narrative)): ?>
                                <div class="py-3 text-center border border-dashed border-slate-200 rounded-lg">
                                    <span class="text-[10px] text-slate-300 italic">Belum ada laporan & dokumen</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- =========================================================
     ANGGOTA TIM & RINCIAN BIAYA — Full Width
     ========================================================= -->
<?php if ($isKeuangan): ?>
    <div class="mt-6">
        <div class="card p-0 overflow-hidden border-t-4 border-t-emerald-500 shadow-md">
            <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-white">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-emerald-500"></i>
                    Anggota Tim & Rincian Biaya
                </h3>
                <span class="text-xs font-bold bg-slate-100 px-2 py-1.5 rounded-md border border-slate-200 text-slate-700 shadow-sm">
                    <?= count($members) ?> Pegawai
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Nama / NIP</th>
                            <th class="px-5 py-3.5 font-semibold">Tingkat</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Harian</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Representasi</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Penginapan</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Transport/Lainnya</th>
                            <th class="px-5 py-3.5 font-semibold text-right text-emerald-700">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400 italic bg-slate-50">Belum ada anggota tim yang didaftarkan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($members as $member): ?>
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-800"><?= esc($member->employee_name) ?></div>
                                        <div class="text-xs text-slate-500 font-mono mt-0.5"><?= esc($member->employee_nip) ?></div>
                                        <?php if (!empty($member->kode_golongan)): ?>
                                            <div class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider"><?= esc($member->kode_golongan) ?> — <?= esc($member->nama_golongan ?? '') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 align-top pt-5">
                                        <?php
                                        $tingkat = '-';
                                        $golSrc = $member->kode_golongan ?: ($member->employee_golongan ?? '');
                                        if ($golSrc) {
                                            $gol = strtoupper($golSrc);
                                            if (strpos($gol, 'IV') !== false) $tingkat = 'A';
                                            elseif (strpos($gol, 'III') !== false) $tingkat = 'B';
                                            elseif (strpos($gol, 'II') !== false && strpos($gol, 'III') === false) $tingkat = 'C';
                                            elseif (strpos($gol, 'I') !== false && strpos($gol, 'II') === false && strpos($gol, 'III') === false && strpos($gol, 'IV') === false) $tingkat = 'D';
                                        }
                                        ?>
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 shadow-sm"><?= $tingkat ?></span>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top pt-5">
                                        <?php if (($member->uang_harian ?? 0) > 0): ?>
                                            <div class="text-slate-700 font-medium"><?= number_format($member->uang_harian ?? 0, 0, ',', '.') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top pt-5">
                                        <?php if (($member->uang_representasi ?? 0) > 0): ?>
                                            <div class="text-slate-700 font-medium"><?= number_format($member->uang_representasi ?? 0, 0, ',', '.') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top pt-5 text-slate-700 font-medium">
                                        <?= number_format($member->penginapan ?? 0, 0, ',', '.') ?>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top pt-4">
                                        <?php $lainnya = ($member->tiket ?? 0) + ($member->transport_darat ?? 0) + ($member->transport_lokal ?? 0); ?>
                                        <div class="text-slate-700 font-medium"><?= number_format($lainnya, 0, ',', '.') ?></div>
                                        <?php if ($lainnya > 0): ?>
                                            <div class="text-[10px] text-slate-400 mt-1">Tiket/Trans Darat/Lokal</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top pt-5 font-bold text-emerald-700 whitespace-nowrap bg-emerald-50/10">
                                        <?= number_format($member->total_biaya ?? 0, 0, ',', '.') ?>

                                        <?php
                                        $isOwnMember = ($member->user_id == auth()->id());
                                        if (($isStaff || $isOwnMember) && $travelRequest->status !== 'draft'):
                                        ?>
                                            <div class="mt-2 pt-2 border-t border-emerald-100">
                                                <a href="<?= base_url('travel/' . $travelRequest->id . '/statement?format=pdf&member_id=' . $member->travel_member_id) ?>" class="text-[10px] text-emerald-600 hover:text-emerald-800 flex items-center justify-end gap-1">
                                                    <i data-lucide="file-check" class="w-3 h-3"></i>
                                                    Surat Pernyataan (PDF)
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($member->expense_items)): ?>
                                            <div class="mt-4 pt-4 border-t border-slate-100 text-left">
                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Rincian Biaya Riil:</div>
                                                <div class="space-y-1.5">
                                                    <?php foreach ($member->expense_items as $item): ?>
                                                        <div class="flex justify-between items-center bg-slate-50/50 p-2 rounded border border-slate-100">
                                                            <div class="flex flex-col">
                                                                <span class="text-[11px] font-bold text-slate-700"><?= esc($item->item_name) ?></span>
                                                                <span class="text-[9px] text-slate-400 uppercase"><?= esc($item->category) ?></span>
                                                            </div>
                                                            <span class="text-[11px] font-mono font-medium text-slate-600">Rp <?= number_format($item->amount, 0, ',', '.') ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-slate-800 border-t border-slate-700">
                        <tr>
                            <td colspan="6" class="px-5 py-4 text-right font-bold text-slate-200 text-sm tracking-wide">GRAND TOTAL BIAYA PERJALANAN</td>
                            <td class="px-5 py-4 text-right font-black text-xl text-emerald-400 whitespace-nowrap">Rp <?= number_format($travelRequest->total_budget ?? 0, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- System Info -->
<div class="text-xs text-center text-slate-400 space-y-1 mt-6 pb-4">
    <p>Dibuat: <?= date('d M Y H:i', strtotime($travelRequest->created_at)) ?></p>
    <?php if ($travelRequest->updated_at && $travelRequest->updated_at !== $travelRequest->created_at): ?>
        <p>Terakhir Diubah: <?= date('d M Y H:i', strtotime($travelRequest->updated_at)) ?></p>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeUploadModal()"></div>
        <div class="relative w-full max-w-md transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900" id="modalTitle">Unggah Dokumen</h3>
                    <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="uploadForm" class="space-y-6">
                    <input type="hidden" id="completenessId" name="completeness_id">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Pilih File (PDF/Gambar)</label>
                        <div class="relative group">
                            <input type="file" id="documentFile" name="document" accept=".pdf,image/*" required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="handleFileSelect(this)">
                            <div class="border-2 border-dashed border-slate-200 rounded-lg p-8 text-center group-hover:border-primary-400 transition-all bg-slate-50">
                                <i data-lucide="upload-cloud" class="w-10 h-10 text-slate-300 mx-auto mb-3 group-hover:text-primary-500 transition-colors"></i>
                                <p class="text-sm text-slate-500 font-medium" id="fileNamePlaceholder">Klik atau seret file ke sini</p>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">Maksimum 5MB (PDF/JPG/PNG)</p>
                            </div>
                        </div>
                    </div>
                    <div id="uploadProgress" class="hidden">
                        <div class="flex justify-between mb-1">
                            <span class="text-xs font-bold text-primary-600 uppercase">Mengunggah...</span>
                            <span class="text-xs font-bold text-primary-600" id="progressPercentage">0%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div id="progressBar" class="h-full bg-primary-500 transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeUploadModal()" class="btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSubmitUpload" class="btn-primary flex-1 justify-center shadow-lg shadow-primary-500/20">Unggah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div id="verifyModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeVerifyModal()"></div>
        <div class="relative w-full max-w-md transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900" id="verifyModalTitle">Verifikasi Dokumen</h3>
                    <button onclick="closeVerifyModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="verifyForm" class="space-y-6">
                    <input type="hidden" id="verifyCompletenessId" name="completeness_id">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Status Verifikasi</label>
                        <select name="status" id="verifyStatus" class="form-input w-full rounded-lg border-slate-200" required onchange="handleVerifyStatusChange(this)">
                            <option value="verified">Sesuai (Verified)</option>
                            <option value="rejected">Tolak (Perlu Unggah Ulang)</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Catatan (Opsional)</label>
                        <textarea name="note" id="verifyNote" rows="3" class="form-input w-full rounded-lg border-slate-200" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeVerifyModal()" class="btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSubmitVerify" class="btn-primary flex-1 justify-center shadow-lg shadow-primary-500/20">Simpan Verifikasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
    let currentCompletenessId = null;

    function openUploadModal(id, name) {
        currentCompletenessId = id;
        document.getElementById('completenessId').value = id;
        document.getElementById('modalTitle').textContent = 'Unggah: ' + name;
        document.getElementById('uploadModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) lucide.createIcons();
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
        document.getElementById('uploadForm').reset();
        document.getElementById('fileNamePlaceholder').textContent = 'Klik atau seret file ke sini';
        document.getElementById('uploadProgress').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileNamePlaceholder').textContent = input.files[0].name;
        }
    }

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const progressDiv = document.getElementById('uploadProgress');
        const progressBar = document.getElementById('progressBar');
        const percentageText = document.getElementById('progressPercentage');
        const btnSubmit = document.getElementById('btnSubmitUpload');
        progressDiv.classList.remove('hidden');
        btnSubmit.disabled = true;
        axios.post(`<?= base_url('travel/completeness') ?>/${currentCompletenessId}/upload`, formData, {
            onUploadProgress: (progressEvent) => {
                const percentage = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                progressBar.style.width = percentage + '%';
                percentageText.textContent = percentage + '%';
            }
        }).then(response => {
            Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false
                })
                .then(() => window.location.reload());
        }).catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: error.response?.data?.message || 'Terjadi kesalahan saat mengunggah.'
            });
            btnSubmit.disabled = false;
            progressDiv.classList.add('hidden');
        });
    });

    function openVerifyModal(id, name) {
        document.getElementById('verifyCompletenessId').value = id;
        document.getElementById('verifyModalTitle').textContent = 'Verifikasi: ' + name;
        document.getElementById('verifyForm').reset();
        document.getElementById('verifyModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) lucide.createIcons();
    }

    function closeVerifyModal() {
        document.getElementById('verifyModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function handleVerifyStatusChange(select) {
        const btn = document.getElementById('btnSubmitVerify');
        if (select.value === 'rejected') {
            btn.classList.remove('btn-primary');
            btn.classList.add('bg-rose-500', 'hover:bg-rose-600', 'text-white');
            document.getElementById('verifyNote').required = true;
            document.getElementById('verifyNote').placeholder = 'Alasan penolakan (Wajib)...';
        } else {
            btn.classList.add('btn-primary');
            btn.classList.remove('bg-rose-500', 'hover:bg-rose-600', 'text-white');
            document.getElementById('verifyNote').required = false;
            document.getElementById('verifyNote').placeholder = 'Tambahkan catatan jika diperlukan...';
        }
    }

    document.getElementById('verifyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('verifyCompletenessId').value;
        const formData = new FormData(this);
        const btnSubmit = document.getElementById('btnSubmitVerify');
        btnSubmit.disabled = true;
        axios.post(`<?= base_url('travel/completeness') ?>/${id}/verify`, formData)
            .then(response => {
                Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.data.message,
                        timer: 2000,
                        showConfirmButton: false
                    })
                    .then(() => window.location.reload());
            }).catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.response?.data?.message || 'Terjadi kesalahan saat verifikasi.'
                });
                btnSubmit.disabled = false;
            });
    });

    async function cancelTravel(id) {
        const {
            isConfirmed
        } = await Swal.fire({
            title: 'Batalkan Perjalanan Dinas?',
            text: "Status perjalanan akan diubah menjadi 'Cancelled' dan proses akan dihentikan secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tutup'
        });
        if (isConfirmed) {
            try {
                const response = await axios.post(`<?= base_url('travel') ?>/${id}/cancel`, {}, {
                    headers: {
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                });
                if (response.data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire('Gagal', response.data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Terjadi kesalahan sistem saat membatalkan perjalanan.', 'error');
            }
        }
    }

    async function completeTravel(id) {
        const {
            isConfirmed
        } = await Swal.fire({
            title: 'Tandai Selesai?',
            text: "Pengajuan akan ditandai sebagai 'Completed' dan akses edit akan dikunci untuk non-Superadmin.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Tandai Selesai!',
            cancelButtonText: 'Batal'
        });
        if (isConfirmed) {
            try {
                const response = await axios.post(`<?= base_url('travel') ?>/${id}/complete`, {}, {
                    headers: {
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                });
                if (response.data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire('Gagal', response.data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Terjadi kesalahan sistem saat menandai perjalanan selesai.', 'error');
            }
        }
    }
</script>
<?= $this->endSection() ?>