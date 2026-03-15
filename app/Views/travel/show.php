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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 mb-2">
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

        <!-- Narasi Laporan (Phase 27) -->
        <?php if (!empty($travelRequest->report_narrative)): ?>
            <div class="card p-6 border-t-4 border-t-blue-600 bg-blue-50/10">
                <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4 pb-4 border-b border-slate-100 uppercase tracking-wider text-sm">
                    <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                    Narasi Laporan / Dokumentasi
                </h3>
                <div class="text-sm text-slate-700 leading-relaxed bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <?= nl2br(esc((string) $travelRequest->report_narrative)) ?>
                </div>
            </div>
        <?php endif; ?>

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

                <!-- <p class="text-xs text-slate-600 mb-4 bg-white p-3 rounded border border-slate-200 shadow-sm">
                    Status saat ini: <strong class="text-slate-800 font-black uppercase"><?= esc($statusLabel) ?></strong>
                </p> -->

                <div class="flex flex-col gap-3">
                    <?php if ($travelRequest->status === 'draft'): ?>
                        <?php if (auth()->user()->inGroup('superadmin')): ?>
                            <a href="<?= base_url('travel/' . $travelRequest->id . '/enrichment') ?>" class="btn-primary w-full justify-center shadow-md hover:shadow-lg transition-all inline-flex items-center gap-2">
                                <i data-lucide="clipboard-check" class="w-4 h-4"></i> Lengkapi Data
                            </a>
                            <p class="text-[10px] text-primary-600 bg-primary-50 p-2 rounded-lg leading-relaxed border border-primary-100">
                                <strong>Status Draft:</strong> Menunggu pengisian rincian biaya riil dan penandatangan oleh bagian Keuangan sebelum dokumen dapat diterbitkan.
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
                        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100 text-[10px] text-emerald-700 leading-relaxed overflow-hidden relative group mb-1">
                            <div class="absolute -right-2 -top-2 opacity-10 group-hover:opacity-20 transition-opacity">
                                <i data-lucide="check-circle-2" class="w-12 h-12"></i>
                            </div>
                            <strong>Status Aktif:</strong> Data Perjalanan Dinas sudah lengkap. Berkas sudah dapat diunduh.
                        </div>
                        <a href="<?= base_url('travel/' . $travelRequest->id . '/enrichment') ?>" class="btn-warning w-full justify-center shadow-md hover:shadow-lg transition-all animate-in fade-in slide-in-from-bottom-2">
                            <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> Edit Kelengkapan
                        </a>
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

        <!-- Document Completeness Checklist (Phase 8) -->
        <?php if ($travelRequest->status !== 'draft' && !empty($completeness)): ?>
            <div class="card p-0 overflow-hidden border-t-4 border-t-amber-500 shadow-lg mt-6">
                <div class="p-4 border-b border-slate-100 bg-white/50 backdrop-blur-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-black text-sm uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <i data-lucide="check-check" class="w-4 h-4 text-amber-500"></i>
                            Kelengkapan Berkas
                        </h3>
                        <?php
                        $total = count($completeness);
                        $verified = count(array_filter($completeness, fn($c) => $c->status === 'verified'));
                        $progress = ($total > 0) ? ($verified / $total) * 100 : 0;
                        ?>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">
                                <?= $verified ?>/<?= $total ?>
                            </span>
                            <?php if ($travelRequest->status === 'active'): ?>
                                <?php
                                    $isVerifOnly = auth()->user()->inGroup('verificator') && !auth()->user()->inGroup('superadmin', 'admin');
                                    $targetUrl = $isVerifOnly ? base_url('documentation/' . $travelRequest->id . '/verification') : base_url('documentation/' . $travelRequest->id);
                                ?>
                                <a href="<?= $targetUrl ?>"
                                   class="text-[10px] font-bold bg-amber-500 text-white px-3 py-1 rounded inline-flex items-center gap-1 hover:bg-amber-600 transition-colors shadow-sm">
                                    <i data-lucide="upload-cloud" class="w-3 h-3"></i> Kelola
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-400 transition-all duration-700 ease-out" style="width: <?= $progress ?>%"></div>
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    <?php foreach ($completeness as $item): ?>
                        <div class="group relative flex flex-col p-3 rounded-xl border transition-all duration-300 <?= ($item->status === 'verified') ? 'border-emerald-100 bg-emerald-50/20' : 'border-slate-100 bg-white hover:border-amber-200 hover:shadow-sm' ?>">

                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 w-8 h-8 rounded-lg flex items-center justify-center shrink-0 <?= ($item->status === 'verified') ? 'bg-emerald-500 text-white' : ($item->status === 'uploaded' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400') ?>">
                                        <?php if ($item->status === 'verified'): ?>
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        <?php elseif ($item->status === 'uploaded'): ?>
                                            <i data-lucide="file-up" class="w-4 h-4 pulse"></i>
                                        <?php else: ?>
                                            <i data-lucide="clock" class="w-4 h-4"></i>
                                        <?php endif; ?>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-slate-800 truncate" title="<?= esc($item->item_name) ?>">
                                            <?= esc($item->item_name) ?>
                                        </div>

                                        <div class="flex items-center gap-2 mt-1">
                                            <?php
                                            $statusMap = [
                                                'pending'  => ['bg-slate-100 text-slate-500', 'Menunggu'],
                                                'uploaded' => ['bg-amber-100 text-amber-700', 'Unggah'],
                                                'verified' => ['bg-emerald-100 text-emerald-700', 'Sesuai'],
                                                'rejected' => ['bg-rose-100 text-rose-700', 'Ditolak'],
                                            ];
                                            $s = $statusMap[$item->status] ?? $statusMap['pending'];
                                            ?>
                                            <span class="text-[9px] font-black uppercase tracking-tighter py-0.5 px-1.5 rounded <?= $s[0] ?>">
                                                <?= $s[1] ?>
                                            </span>

                                            <?php if ($item->status === 'verified' && !empty($item->verified_at)): ?>
                                                <span class="text-[9px] text-slate-400 font-medium whitespace-nowrap">
                                                    <?= date('d/m H:i', strtotime($item->verified_at)) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <?php if (!empty($item->files)): ?>
                                        <div class="flex -space-x-2 mr-2">
                                            <?php foreach ($item->files as $fileIdx => $file): ?>
                                                <a href="<?= base_url('download/file/' . $file->id) ?>"
                                                    class="w-7 h-7 flex items-center justify-center rounded-full bg-white text-blue-500 hover:bg-blue-50 transition-all border border-blue-100 shadow-sm"
                                                    title="<?= esc($file->original_name) ?>" target="_blank">
                                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif (!empty($item->document_path)): ?>
                                        <a href="<?= base_url('travel/completeness/' . $item->id . '/download') ?>"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-50 text-slate-500 hover:bg-primary-50 hover:text-primary-600 transition-colors border border-slate-200"
                                            title="Lihat Dokumen" target="_blank">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Verify Button -->
                                    <?php if (auth()->user()->inGroup('superadmin', 'verificator') && $item->status === 'uploaded'): ?>
                                        <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all border border-emerald-200"
                                            title="Verifikasi"
                                            onclick="openVerifyModal(<?= $item->id ?>, '<?= esc($item->item_name) ?>')">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($item->verification_note)): ?>
                                <div class="mt-2 text-[10px] text-slate-500 bg-slate-50/50 p-2 rounded-lg border border-slate-100 italic leading-relaxed">
                                    "<?= esc($item->verification_note) ?>"
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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


<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeUploadModal()"></div>

        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all">
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
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center group-hover:border-primary-400 transition-all bg-slate-50">
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

        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all">
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
                        <select name="status" id="verifyStatus" class="form-input w-full rounded-xl border-slate-200" required onchange="handleVerifyStatusChange(this)">
                            <option value="verified">Sesuai (Verified)</option>
                            <option value="rejected">Tolak (Perlu Unggah Ulang)</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Catatan (Opsional)</label>
                        <textarea name="note" id="verifyNote" rows="3" class="form-input w-full rounded-xl border-slate-200" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
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
            })
            .then(response => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.response?.data?.message || 'Terjadi kesalahan saat mengunggah.'
                });
                btnSubmit.disabled = false;
                progressDiv.classList.add('hidden');
            });
    });

    // Verification Logic
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
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.response?.data?.message || 'Terjadi kesalahan saat verifikasi.'
                });
                btnSubmit.disabled = false;
            });
    });
</script>
<?= $this->endSection() ?>