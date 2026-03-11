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
        <a href="<?= base_url('admin/travel') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>

        <?php if ($travelRequest->status === 'draft' || $travelRequest->status === 'pending'): ?>
            <a href="<?= base_url('admin/travel/' . $travelRequest->id . '/edit') ?>" class="btn-accent inline-flex items-center gap-2 text-sm">
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
                    'draft' => 'bg-slate-200 text-slate-700',
                    'pending' => 'bg-yellow-200 text-yellow-700',
                    'verified' => 'bg-blue-200 text-blue-700',
                    'approved' => 'bg-emerald-200 text-emerald-700',
                    'rejected' => 'bg-red-200 text-red-700',
                    'cancelled' => 'bg-orange-200 text-orange-700'
                ];
                $statusClass = $statusColors[$travelRequest->status] ?? $statusColors['draft'];
                ?>
                <span class="px-3 py-1 text-xs font-bold rounded shadow-sm uppercase tracking-wider <?= $statusClass ?>">
                    Status: <?= esc($travelRequest->status) ?>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Mata Anggaran (MAK)</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->mak ?: '-') ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tgl Surat Tugas</span>
                    <span class="text-sm font-medium text-slate-900"><?= esc($travelRequest->tgl_surat_tugas ? date('d F Y', strtotime($travelRequest->tgl_surat_tugas)) : '-') ?></span>
                </div>
                <div class="md:col-span-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Maksud / Tujuan Acara</span>
                    <p class="text-sm text-slate-800 bg-slate-50 p-3 rounded-md border border-slate-100 min-h-16"><?= nl2br(esc((string) $travelRequest->purpose)) ?></p>
                </div>
            </div>
        </div>

        <!-- Tujuan & Jadwal -->
        <div class="card p-6 border-t-4 border-t-blue-500">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 mb-4 pb-4 border-b border-slate-100">
                <i data-lucide="map-pin" class="w-5 h-5 text-blue-500"></i>
                Rute & Jadwal
                <span class="text-[11px] font-bold text-blue-600 bg-blue-50/80 backdrop-blur-sm px-3 py-1 rounded-full border border-blue-100 shadow-sm whitespace-nowrap">
                    <i data-lucide="calendar-clock" class="w-3.5 h-3.5 inline mr-1 opacity-70"></i>
                    <?= esc($travelRequest->duration_days) ?> Hari
                </span>
            </h3>

            <div class="flex flex-col md:flex-row gap-6 relative px-2">
                <!-- Departure -->
                <div class="flex-1">
                    <div class="flex items-start gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-accent-100 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="plane-takeoff" class="w-4 h-4 text-accent-800"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-accent-600 uppercase tracking-wider">Keberangkatan</span>
                            <span class="text-base font-bold text-slate-900 block leading-tight mt-1"><?= esc($travelRequest->origin) ?></span>
                        </div>
                    </div>
                    <div class="ml-11">
                        <span class="text-sm text-slate-700 font-medium block"><?= date('d F Y', strtotime($travelRequest->departure_date)) ?></span>
                        <span class="inline-block mt-2 px-2 py-1 bg-slate-50 text-slate-600 text-xs rounded border border-slate-200 font-medium shadow-sm">
                            <i data-lucide="train" class="w-3 h-3 inline mr-1"></i> <?= ucfirst(esc((string) $travelRequest->transportation_type)) ?>
                        </span>
                    </div>
                </div>

                <!-- Destination -->
                <div class="flex-1">
                    <div class="flex items-start gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-blue-600"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-blue-500 uppercase tracking-wider">Tujuan Akhir</span>
                            <span class="text-base font-bold text-slate-900 block leading-tight mt-1"><?= esc($travelRequest->destination) ?></span>
                        </div>
                    </div>
                    <div class="ml-11">
                        <span class="text-sm text-slate-700 mt-1 block">
                            <?= esc((string) ($travelRequest->destination_city ? $travelRequest->destination_city . ', ' . $travelRequest->destination_province : $travelRequest->destination_province)) ?>
                        </span>
                        <span class="text-xs text-slate-500 mt-1 font-medium bg-slate-50 inline-block px-2 py-0.5 rounded border border-slate-200">Kembali: <?= date('d F Y', strtotime($travelRequest->return_date)) ?></span>
                    </div>
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
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400 italic bg-slate-50">Belum ada anggota tim yang didaftarkan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($members as $member): ?>
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-800"><?= esc($member->employee_name) ?></div>
                                        <div class="text-xs text-slate-500 font-mono mt-0.5"><?= esc($member->employee_nip) ?></div>
                                        <div class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider"><?= esc($member->employee_golongan ?? '-') ?></div>
                                    </td>
                                    <td class="px-5 py-4 align-top pt-5">
                                        <?php
                                        $tingkat = '-';
                                        if (isset($member->employee_golongan) && $member->employee_golongan) {
                                            $gol = strtoupper($member->employee_golongan);
                                            // Handle "GOLONGAN IV", "GOLONGAN III", dll
                                            if (strpos($gol, 'IV') !== false) $tingkat = 'A';
                                            elseif (strpos($gol, 'III') !== false) $tingkat = 'B';
                                            elseif (strpos($gol, 'II') !== false && strpos($gol, 'III') === false) $tingkat = 'C';
                                            elseif (strpos($gol, 'I') !== false && strpos($gol, 'II') === false && strpos($gol, 'III') === false && strpos($gol, 'IV') === false) $tingkat = 'D';
                                        }
                                        ?>
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 shadow-sm"><?= $tingkat ?></span>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top pt-5">
                                        <?php if ($member->uang_harian ?? 0 > 0): ?>
                                            <div class="text-slate-700 font-medium"><?= number_format($member->uang_harian ?? 0, 0, ',', '.') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top pt-5">
                                        <?php if (($member->uang_representasi ?? 0) > 0): ?>
                                            <div class="text-slate-700 font-medium"> <?= number_format($member->uang_representasi ?? 0, 0, ',', '.') ?></div>
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

        <!-- Penandatangan -->
        <div class="card p-6 border-t-4 border-t-indigo-500 shadow-md">
            <h3 class="font-bold text-sm uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-100 flex justify-between items-center">
                <span>Pejabat Berwenang</span>
                <i data-lucide="shield-check" class="w-4 h-4 text-slate-400"></i>
            </h3>

            <div class="space-y-5">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 mb-2">Pejabat Pembuat Komitmen (PPK)</span>
                    <?php if ($ppk): ?>
                        <div class="flex flex-col bg-slate-50 p-3 rounded-lg border border-slate-200 shadow-sm relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-10 h-10 bg-indigo-50/50 rounded-bl-full flex items-start justify-end p-2">
                                <i data-lucide="pen-tool" class="w-3 h-3 text-indigo-300"></i>
                            </div>
                            <span class="block text-sm font-bold text-slate-800"><?= esc($ppk->employee_name) ?></span>
                            <span class="block text-xs font-mono text-slate-500 mt-1"><?= esc($ppk->nip) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="bg-slate-50 p-2 rounded border border-slate-100 text-sm italic text-slate-400 text-center">Belum ditentukan</div>
                    <?php endif; ?>
                </div>

                <div>
                    <span class="block text-xs font-semibold text-slate-400 mb-2">Kuasa Pengguna Anggaran (KPA)</span>
                    <?php if ($kpa): ?>
                        <div class="flex flex-col bg-slate-50 p-3 rounded-lg border border-slate-200 shadow-sm relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-10 h-10 bg-purple-50/50 rounded-bl-full flex items-start justify-end p-2">
                                <i data-lucide="check-square" class="w-3 h-3 text-purple-300"></i>
                            </div>
                            <span class="block text-sm font-bold text-slate-800"><?= esc($kpa->employee_name) ?></span>
                            <span class="block text-xs font-mono text-slate-500 mt-1"><?= esc($kpa->nip) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="bg-slate-50 p-2 rounded border border-slate-100 text-sm italic text-slate-400 text-center">Belum ditentukan</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action / Workflow Panel -->
        <div class="card p-6 border-t-4 border-t-amber-500 bg-amber-50/30 shadow-md">
            <h3 class="font-bold text-xs uppercase tracking-wider text-amber-800 mb-4 pb-2 border-b border-amber-200/50">Tindakan Administratif</h3>

            <p class="text-xs text-slate-600 mb-4 bg-white p-3 rounded border border-slate-200 shadow-sm">Pengajuan Anda saat ini diproses pada tahap: <strong class="text-slate-800 font-black uppercase"><?= esc($travelRequest->status) ?></strong>.</p>

            <div class="flex flex-col gap-2.5">
                <?php if ($travelRequest->status === 'draft'): ?>
                    <form action="<?= base_url('admin/travel/' . $travelRequest->id . '/submit') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-primary w-full justify-center shadow-md hover:shadow-lg transition-all" onclick="return confirm('Apakah Anda yakin ingin mengajukan permintaan ini ke verifikator?')">
                            <i data-lucide="send" class="w-4 h-4 mr-2"></i> Ajukan Permintaan
                        </button>
                    </form>

                    <form action="<?= base_url('admin/travel/' . $travelRequest->id . '/destroy') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-danger w-full justify-center shadow-md hover:shadow-lg transition-all" onclick="return confirm('Apakah Anda yakin ingin menghapus permintaan dinas ini secara permanen?')">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Hapus Permintaan
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($travelRequest->status === 'pending'): ?>
                    <form action="<?= base_url('admin/travel/' . $travelRequest->id . '/cancel') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-danger w-full justify-center shadow-md hover:shadow-lg transition-all" onclick="return confirm('Batalkan pengajuan ini? Status akan kembali menjadi draft.')">
                            <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Batalkan Pengajuan
                        </button>
                    </form>

                    <button type="button" class="btn-primary w-full justify-center shadow-md opacity-50 cursor-not-allowed" disabled>
                        <i data-lucide="shield-question" class="w-4 h-4 mr-2"></i> Menunggu Verifikasi
                    </button>
                    <p class="text-[10px] text-center text-slate-400 mt-1">Hanya Verifikator yang dapat memverifikasi.</p>
                <?php endif; ?>
            </div>
        </div>

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
<!-- Scripts can be added here if needed -->
<?= $this->endSection() ?>