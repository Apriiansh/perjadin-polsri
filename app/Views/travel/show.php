<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-1 text-sm text-slate-500">Nomor Surat Tugas: <?= esc($travelRequest->no_surat_tugas ?? 'Belum ada nomor') ?></p>
    </div>

    <div class="flex items-center gap-2">
        <a href="<?= base_url('travel') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>

        <?php if (($isStaff ?? false) && $travelRequest->status === 'draft'): ?>
            <a href="<?= base_url('travel/' . $travelRequest->id . '/edit') ?>" class="btn-primary inline-flex items-center gap-2 text-sm">
                <i data-lucide="edit" class="w-4 h-4"></i>
                Edit
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left Column: Primary Details -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Document & Maksud Perjalanan -->
        <div class="card p-6 border-t-4 border-t-primary-500">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-slate-100">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-primary-500"></i>
                    Informasi Dokumen
                </h3>

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
                <span class="px-3 py-1 text-xs font-bold rounded shadow-sm uppercase tracking-wider <?= $statusClass ?>">
                    <?= esc($statusLabel) ?>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nomor Surat Tugas</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->no_surat_tugas ?: '-') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tgl Surat Tugas</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->tgl_surat_tugas ? date('d F Y', strtotime($travelRequest->tgl_surat_tugas)) : '-') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Beban Anggaran</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->budget_burden_by ?: '-') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tahun Anggaran</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->tahun_anggaran ?: '-') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Lokasi / Venue</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->lokasi ?: '-') ?></span>
                </div>
            </div>
        </div>

        <!-- Surat Rujukan -->
        <?php if (!empty($travelRequest->nomor_surat_rujukan) || !empty($travelRequest->instansi_pengirim_rujukan) || !empty($travelRequest->perihal_surat_rujukan)): ?>
            <div class="card p-6 border-t-4 border-t-violet-500">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 mb-4 pb-4 border-b border-slate-100">
                    <i data-lucide="mail" class="w-5 h-5 text-violet-500"></i>
                    Surat Rujukan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                    <?php if (!empty($travelRequest->nomor_surat_rujukan)): ?>
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nomor Surat</span>
                            <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->nomor_surat_rujukan) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($travelRequest->tgl_surat_rujukan)): ?>
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Surat</span>
                            <span class="text-sm font-medium text-slate-900"><?= date('d F Y', strtotime($travelRequest->tgl_surat_rujukan)) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($travelRequest->instansi_pengirim_rujukan)): ?>
                        <div class="md:col-span-2">
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Instansi Pengirim</span>
                            <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->instansi_pengirim_rujukan) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($travelRequest->perihal_surat_rujukan)): ?>
                        <div class="md:col-span-2">
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Perihal</span>
                            <p class="text-sm text-slate-800 bg-slate-50 p-3 rounded-md border border-slate-100"><?= nl2br(esc((string) $travelRequest->perihal_surat_rujukan)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tujuan & Jadwal -->
        <div class="card p-6 border-t-4 border-t-blue-500">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 mb-4 pb-4 border-b border-slate-100">
                <i data-lucide="map-pin" class="w-5 h-5 text-blue-500"></i>
                Tujuan & Jadwal Kegiatan
                <span class="text-[11px] font-bold text-blue-600 bg-blue-50/80 backdrop-blur-sm px-3 py-1 rounded-full border border-blue-100 shadow-sm whitespace-nowrap">
                    <i data-lucide="calendar-clock" class="w-3.5 h-3.5 inline mr-1 opacity-70"></i>
                    <?= esc($travelRequest->duration_days) ?> Hari
                </span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 mb-6">
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tempat Berangkat</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->departure_place ?: '-') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tempat Tujuan</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->destination_province) ?> - <?= esc($travelRequest->destination_city ?: '-') ?></span>
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

        <!-- Anggota Tim & Rincian Biaya -->
        <div class="card p-0 overflow-hidden border-t-4 border-t-emerald-500 shadow-md">
            <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-white">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-emerald-500"></i>
                    Anggota Tim & Rincian
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
                                        $isOwnMember = (auth()->user()->employee_id == $member->employee_id);
                                        if (($isStaff || $isOwnMember) && $travelRequest->status !== 'draft'):
                                        ?>
                                            <div class="mt-2 pt-2 border-t border-emerald-100">
                                                <a href="<?= base_url('travel/' . $travelRequest->id . '/statement?member_id=' . $member->travel_member_id) ?>" class="text-[10px] text-emerald-600 hover:text-emerald-800 flex items-center justify-end gap-1">
                                                    <i data-lucide="file-check" class="w-3 h-3"></i>
                                                    Surat Pernyataan
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Itemized Expenses (Phase 8) -->
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

        <!-- Document Completeness Checklist (Phase 8) -->
        <?php if ($travelRequest->status !== 'draft' && !empty($completeness)): ?>
            <div class="card p-0 overflow-hidden border-t-4 border-t-amber-500 shadow-md mt-6">
                <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-white">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i data-lucide="check-check" class="w-5 h-5 text-amber-500"></i>
                        Checklist Kelengkapan Dokumen
                    </h3>
                    <?php
                    $total = count($completeness);
                    $verified = count(array_filter($completeness, fn($c) => $c->status === 'verified'));
                    $progress = ($total > 0) ? ($verified / $total) * 100 : 0;
                    ?>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-[11px] font-bold text-slate-400 uppercase">Progres Verifikasi</div>
                            <div class="text-sm font-black text-slate-700"><?= $verified ?> / <?= $total ?> Selesai</div>
                        </div>
                        <div class="w-24 h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                            <div class="h-full bg-amber-400 transition-all duration-500" style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($completeness as $item): ?>
                            <div class="flex items-center justify-between p-4 rounded-xl border-2 transition-all <?= ($item->status === 'verified') ? 'border-emerald-100 bg-emerald-50/30' : 'border-slate-100 bg-white hover:border-slate-200' ?>">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center <?= ($item->status === 'verified') ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400' ?>">
                                        <?php if ($item->status === 'verified'): ?>
                                            <i data-lucide="check" class="w-5 h-5"></i>
                                        <?php else: ?>
                                            <i data-lucide="clock" class="w-5 h-5"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800"><?= esc($item->item_name) ?></div>
                                        <div class="text-[11px] font-bold uppercase tracking-wider mt-0.5 <?= ($item->status === 'verified') ? 'text-emerald-600' : 'text-slate-400' ?>">
                                            <?= esc($item->status) ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <?php if (!empty($item->document_path)): ?>
                                        <a href="<?= base_url('completeness/download/' . $item->id) ?>" class="btn-secondary p-2 rounded-lg" title="Lihat Dokumen">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($item->status !== 'verified'): ?>
                                        <!-- Dosen Upload Button (Phase 8b) -->
                                        <button class="btn-accent p-2 rounded-lg" title="Unggah Dokumen" onclick="alert('Fitur unggah akan hadir di Phase 8b')">
                                            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Right Column: Sidebar Actions & Info -->
    <div class="space-y-6">

        <!-- Unduh Dokumen -->
        <div class="card p-6 border-t-4 border-t-indigo-500 shadow-md">
            <h3 class="font-bold text-sm uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-100 flex justify-between items-center">
                <span>Unduh Dokumen</span>
                <i data-lucide="download" class="w-4 h-4 text-slate-400"></i>
            </h3>

            <div class="space-y-4">
                <!-- Lampiran Surat Tugas -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2">Lampiran Surat Tugas</label>
                    <?php if (!empty($travelRequest->lampiran_path)): ?>
                        <a href="<?= base_url('travel/' . $travelRequest->id . '/lampiran') ?>" class="btn-accent w-full justify-center inline-flex items-center gap-2 text-sm">
                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                            <?= esc($travelRequest->lampiran_original_name ?: 'Download Lampiran') ?>
                        </a>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 italic bg-slate-50 p-3 rounded border border-dashed border-slate-200 text-center">Belum ada lampiran ST yang diunggah.</p>
                    <?php endif; ?>
                </div>

                <hr class="border-slate-100">
                <div class="pt-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Dokumen Terbitan Sistem</label>
                    <?php if ($travelRequest->status !== 'draft'): ?>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- SPD Generate -->
                            <a id="btn-download-spd" href="<?= base_url('travel/' . $travelRequest->id . '/spd') ?>"
                                class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-slate-100 bg-slate-50 hover:bg-white hover:border-primary-500 hover:text-primary-600 transition-all group gap-2 text-center">
                                <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-primary-50">
                                    <i data-lucide="file-text" class="w-5 h-5 text-primary-500"></i>
                                </div>
                                <span class="text-xs font-bold">SPD</span>
                            </a>

                            <!-- Surat Pernyataan Generate -->
                            <a href="<?= base_url('travel/' . $travelRequest->id . '/statement') ?>"
                                class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-slate-100 bg-slate-50 hover:bg-white hover:border-emerald-500 hover:text-emerald-600 transition-all group gap-2 text-center">
                                <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-emerald-50">
                                    <i data-lucide="file-check" class="w-5 h-5 text-emerald-500"></i>
                                </div>
                                <span class="text-xs font-bold"><?= $isStaff ? 'Surat Pernyataan' : 'Pernyataan (.docx)' ?></span>
                            </a>

                            <!-- Daftar Kontrol Generate (Admin/Staff only) -->
                            <?php if ($isStaff): ?>
                                <a href="<?= base_url('travel/' . $travelRequest->id . '/control-list') ?>"
                                    class="col-span-2 flex items-center justify-between p-4 rounded-xl border-2 border-slate-100 bg-emerald-50/20 hover:bg-white hover:border-emerald-600 hover:text-emerald-700 transition-all group gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                                            <i data-lucide="file-spreadsheet" class="w-5 h-5 text-emerald-600"></i>
                                        </div>
                                        <div class="text-left">
                                            <span class="block text-xs font-bold uppercase tracking-tight">Daftar Kontrol Pembayaran</span>
                                            <span class="text-[9px] text-slate-400 font-medium uppercase">Export Format Excel (.xlsx)</span>
                                        </div>
                                    </div>
                                    <i data-lucide="download" class="w-4 h-4 text-emerald-400"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-indigo-50/50 border border-dashed border-indigo-200 rounded-xl p-4 text-center">
                            <div class="w-10 h-10 rounded-full bg-white mx-auto mb-3 flex items-center justify-center shadow-sm">
                                <i data-lucide="lock" class="w-5 h-5 text-indigo-400"></i>
                            </div>
                            <p class="text-[11px] font-medium text-indigo-600 leading-relaxed px-2">Dokumen (SPD & Pernyataan) otomatis terlampir setelah data dilengkapi Keuangan.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Action / Workflow Panel -->
        <?php if ($isStaff ?? false): ?>
            <div class="card p-6 border-t-4 border-t-amber-500 bg-amber-50/30 shadow-md">
                <h3 class="font-bold text-xs uppercase tracking-wider text-amber-800 mb-4 pb-2 border-b border-amber-200/50">Tindakan Administratif</h3>

                <p class="text-xs text-slate-600 mb-4 bg-white p-3 rounded border border-slate-200 shadow-sm">
                    Status saat ini: <strong class="text-slate-800 font-black uppercase"><?= esc($statusLabel) ?></strong>
                </p>

                <div class="flex flex-col gap-2.5">
                    <?php if ($travelRequest->status === 'draft'): ?>
                        <?php if (auth()->user()->inGroup('superadmin')): ?>
                            <a href="<?= base_url('travel/' . $travelRequest->id . '/enrichment') ?>" class="btn-primary w-full justify-center shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2">
                                <i data-lucide="clipboard-check" class="w-4 h-4"></i> Lengkapi Data
                            </a>
                        <?php endif; ?>
                        <form action="<?= base_url('travel/' . $travelRequest->id . '/destroy') ?>" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-danger w-full justify-center shadow-md hover:shadow-lg transition-all" onclick="return confirm('Apakah Anda yakin ingin menghapus permintaan dinas ini secara permanen?')">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Hapus
                            </button>
                        </form>
                    <?php elseif ($travelRequest->status === 'active'): ?>
                        <a href="<?= base_url('travel/' . $travelRequest->id . '/enrichment') ?>" class="btn-warning w-full justify-center shadow-md hover:shadow-lg transition-all animate-in fade-in slide-in-from-bottom-2">
                            <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> Edit Kelengkapan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- System Info -->
        <div class="text-xs text-center text-slate-400 space-y-1">
            <p>Dibuat: <?= date('d M Y H:i', strtotime($travelRequest->created_at)) ?></p>
            <?php if ($travelRequest->updated_at && $travelRequest->updated_at !== $travelRequest->created_at): ?>
                <p>Terakhir Diubah: <?= date('d M Y H:i', strtotime($travelRequest->updated_at)) ?></p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
    // Placeholder — scripts untuk halaman detail akan ditambahkan saat form kelengkapan siap
</script>
<?= $this->endSection() ?>