<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-1 text-sm text-slate-500">
            Dokumentasi Individu: <span class="font-bold text-primary-600"><?= esc($member->employee_name ?? auth()->user()->username) ?></span>
            <span class="mx-2 text-slate-300">|</span>
            No Surat Tugas: <span class="font-bold text-slate-800"><?= esc($travelRequest->no_surat_tugas) ?></span>
        </p>
    </div>

    <div class="flex items-center gap-2">
        <div class="hidden md:flex flex-col items-end mr-2">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Data</span>
            <span class="badge-success text-[10px] px-2 py-0.5">Aktif</span>
        </div>
        <a href="<?= base_url('travel') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>
</div>

<?php if (auth()->user()->inGroup('superadmin', 'verificator')): ?>
    <div class="alert-info mb-6 flex items-center gap-3">
        <i data-lucide="shield-alert" class="w-5 h-5"></i>
        <p class="text-sm font-medium">Anda sedang melihat/mengubah dokumentasi sebagai <strong>Superadmin/Verifikator</strong>. Perubahan yang Anda simpan akan masuk ke catatan individu anggota ini.</p>
    </div>
<?php endif; ?>

<?php
// Compute global status for the member
$memberStatus = 'pending';
$globalNote = '';
$hasRejected = false;
$allVerified = true;
$hasUploaded = false;

foreach ($completeness as $item) {
    if ($item->status === 'rejected') {
        $hasRejected = true;
        $globalNote = $item->verification_note;
    }
    if ($item->status !== 'verified') {
        $allVerified = false;
    }
    if ($item->status === 'uploaded' || !empty($item->files)) {
        $hasUploaded = true;
    }
}

if ($hasRejected) {
    $memberStatus = 'rejected';
} elseif ($allVerified && !empty($completeness)) {
    $memberStatus = 'verified';
} elseif ($hasUploaded) {
    $memberStatus = 'uploaded';
}

$statusLabel = 'BELUM ADA FILE';
$statusClass = 'bg-slate-100 text-slate-400';

if ($memberStatus === 'verified') {
    $statusLabel = 'TERVERIFIKASI';
    $statusClass = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
} elseif ($memberStatus === 'rejected') {
    $statusLabel = 'DITOLAK / PERLU REVISI';
    $statusClass = 'bg-rose-50 text-rose-600 border border-rose-100';
} elseif ($memberStatus === 'uploaded') {
    $statusLabel = 'MENUNGGU VERIFIKASI';
    $statusClass = 'bg-amber-50 text-amber-600 border border-amber-100 shadow-sm';
}
?>

<!-- Global Status & Note -->
<div class="mb-6 space-y-3 animate-in">
    <div class="card p-4 flex flex-wrap items-center justify-between gap-4 border-l-4 <?= $memberStatus === 'rejected' ? 'border-l-rose-500' : ($memberStatus === 'verified' ? 'border-l-emerald-500' : 'border-l-slate-300') ?>">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl flex items-center justify-center <?= $statusClass ?>">
                <i data-lucide="<?= $memberStatus === 'rejected' ? 'alert-triangle' : ($memberStatus === 'verified' ? 'check-check' : 'clock') ?>" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Status Verifikasi Laporan</p>
                <h3 class="text-sm font-black <?= $memberStatus === 'rejected' ? 'text-rose-700' : ($memberStatus === 'verified' ? 'text-emerald-700' : 'text-slate-700') ?>">
                    <?= $statusLabel ?>
                </h3>
            </div>
        </div>

        <?php if ($memberStatus === 'rejected'): ?>
            <div class="flex-1 min-w-[300px] lg:max-w-md p-3 bg-rose-50 rounded-xl border border-rose-100 flex items-start gap-2.5 animate-pulse-subtle">
                <i data-lucide="info" class="w-4 h-4 text-rose-500 mt-0.5"></i>
                <div>
                    <p class="text-[9px] font-black text-rose-600 uppercase tracking-widest leading-none mb-1">Catatan Verifikator</p>
                    <p class="text-[11px] text-rose-700 font-bold leading-relaxed"><?= esc($globalNote) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<form action="<?= base_url('documentation/' . $travelRequest->id) ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side: Checklist Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Narrative Report (Phase 27) -->
            <div class="card p-0 border-none shadow-premium bg-white overflow-hidden">
                <div class="bg-primary-500 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-white">
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-sm uppercase tracking-widest">Narasi Laporan</h3>
                            <p class="text-[10px] text-slate-100 font-medium">Berikan ringkasan pelaksanaan perjalanan dinas</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 bg-slate-50/30">
                    <textarea
                        name="report_narrative"
                        rows="3"
                        class="form-input border-2 border-primary-400 p-4 w-full text-sm bg-white focus:border-primary-500 focus:ring-primary-500/90 transition-all rounded-xl shadow-inner-sm"
                        <?= ($travelRequest->status === 'completed' && !auth()->user()->inGroup('superadmin')) ? 'readonly' : '' ?>
                        placeholder="Contoh: Telah dilaksanakan koordinasi dengan pihak terkait mengenai..."><?= esc((string) ($member->report_narrative ?? '')) ?></textarea>
                    <div class="mt-3 flex items-start gap-2 text-[10px] text-slate-500 italic px-1">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-blue-500 mt-0.5"></i>
                        <span>Narasi ini bersifat individu dan akan muncul pada lampiran dokumen laporan akhir perjalanan dinas.</span>
                    </div>
                </div>
            </div>

            <!-- Documentation Checklist -->
            <div class="card p-0 border-none shadow-premium bg-white overflow-hidden">
                <div class="bg-secondary-500 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-white">
                            <i data-lucide="list-checks" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-sm uppercase tracking-widest">Kelengkapan Berkas</h3>
                            <p class="text-[10px] text-amber-100 font-medium tracking-wide">Unggah semua bukti fisik pendukung wajib</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <?php if (empty($completeness)): ?>
                        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                                <i data-lucide="hourglass" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700">Belum Ada Checklist</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Daftar berkas akan muncul setelah diverifikasi awal oleh bagian Keuangan.</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($completeness as $item): ?>
                        <div class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white hover:border-secondary-200 hover:shadow-xl hover:shadow-secondary-500/5 transition-all duration-300 p-0">
                            <div class="flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
                                <!-- Info Section -->
                                <div class="flex-1 p-5">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h4 class="font-black text-slate-800 text-sm leading-tight group-hover:text-amber-600 transition-colors"><?= esc($item->item_name) ?></h4>
                                            <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-tight"><?= esc($item->remark ?: 'Wajib diunggah (Nota/Tiket/Bukti Fisik)') ?></p>
                                        </div>
                                    </div>

                                    <!-- Existing Files -->
                                    <div class="space-y-2">
                                        <?php if (!empty($item->files)): ?>
                                            <div class="grid grid-cols-1 gap-2">
                                                <?php foreach ($item->files as $file): ?>
                                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/50 border border-slate-100 group/file hover:bg-white hover:border-blue-100 transition-all">
                                                        <div class="flex items-center gap-3 min-w-0">
                                                            <div class="h-8 w-8 rounded-lg bg-white flex items-center justify-center shadow-sm text-blue-500">
                                                                <i data-lucide="file" class="w-4 h-4"></i>
                                                            </div>
                                                            <div class="flex flex-col min-w-0">
                                                                <span class="text-[10px] font-bold text-slate-700 truncate" title="<?= esc($file->original_name) ?>">
                                                                    <?= esc($file->original_name) ?>
                                                                </span>
                                                                <span class="text-[8px] text-slate-400 font-mono uppercase tracking-widest">
                                                                    <?= number_format($file->file_size / 1024, 1) ?> KB
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <?php if (!($travelRequest->status === 'completed' && !auth()->user()->inGroup('superadmin'))): ?>
                                                            <button type="button"
                                                                onclick="deleteFile(<?= $file->id ?>, this)"
                                                                class="h-7 w-7 flex items-center justify-center text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all opacity-0 group-hover/file:opacity-100"
                                                                title="Hapus File">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="p-3 bg-slate-50/50 rounded-xl border border-dashed border-slate-200 flex flex-col items-center justify-center">
                                                <i data-lucide="file-warning" class="w-5 h-5 text-slate-300 mb-1"></i>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic">Belum ada file diunggah</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Upload Section -->
                                <?php if (!($travelRequest->status === 'completed' && !auth()->user()->inGroup('superadmin'))): ?>
                                    <div class="w-full lg:w-72 p-5 bg-slate-50/30 group-hover:bg-white transition-colors">
                                        <div class="relative h-full min-h-[100px]">
                                            <input type="file"
                                                name="documents_<?= $item->id ?>[]"
                                                id="file_<?= $item->id ?>"
                                                multiple
                                                accept=".pdf,.docx,image/*"
                                                class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer"
                                                onchange="updateFileNameDisplay(this, 'display_<?= $item->id ?>')">
                                            <div class="h-full border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center p-4 gap-2 group-hover:border-amber-400 group-hover:bg-amber-50/30 transition-all bg-white/50">
                                                <div class="h-10 w-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 group-hover:bg-amber-100 group-hover:text-amber-500 transition-all">
                                                    <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                                </div>
                                                <div class="text-center">
                                                    <p id="display_<?= $item->id ?>" class="text-[10px] font-black text-slate-500 truncate px-2 group-hover:text-amber-700">PILIH BERKAS</p>
                                                    <p class="text-[8px] text-slate-400 font-bold uppercase mt-0.5 group-hover:text-amber-500">Multifile supported</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Side: Info & Submit -->
        <div class="space-y-6">
            <div class="card p-6 border-t-4 border-t-primary-500 sticky top-6 shadow-xl">
                <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2 uppercase tracking-tight">
                    <i data-lucide="info" class="w-4 h-4 text-primary-500"></i>
                    Petunjuk Upload
                </h3>

                <ul class="space-y-4 text-[11px] text-slate-600 leading-relaxed mb-6">
                    <li class="flex gap-2">
                        <span class="w-4 h-4 shrink-0 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold">1</span>
                        <span>Anda dapat <strong>memilih beberapa file</strong> sekaligus untuk satu item checklist.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="w-4 h-4 shrink-0 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold">2</span>
                        <span>Ukuran maksimal setiap file adalah <strong>2MB</strong>.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="w-4 h-4 shrink-0 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold">3</span>
                        <span>Format yang didukung: <strong>PDF, DOCX, JPG, PNG</strong>.</span>
                    </li>
                </ul>

                <?php if (!($travelRequest->status === 'completed' && !auth()->user()->inGroup('superadmin'))): ?>
                    <button type="submit" class="btn-success w-full py-3 shadow-lg hover:shadow-primary-200/50 transition-all flex items-center justify-center gap-3">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        <span class="uppercase font-black tracking-widest text-xs">Simpan Semua</span>
                    </button>
                    <p class="mt-4 text-[10px] text-center text-slate-400 italic">Pastikan seluruh berkas sudah benar sebelum dikirim untuk verifikasi.</p>
                <?php else: ?>
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-center">
                        <i data-lucide="lock" class="w-8 h-8 text-blue-400 mx-auto mb-2"></i>
                        <p class="text-[11px] font-bold text-blue-700 uppercase tracking-widest">Dokumentasi Terkunci</p>
                        <p class="text-[10px] text-blue-600 mt-1">Status perjalanan sudah selesai.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<script>
    function updateFileNameDisplay(input, displayId) {
        const display = document.getElementById(displayId);
        const files = input.files;

        if (files.length === 0) {
            display.innerText = "Klik atau Drop File";
            display.classList.remove('text-amber-600');
        } else if (files.length === 1) {
            display.innerText = files[0].name;
            display.classList.add('text-amber-600');
        } else {
            display.innerText = files.length + " File Terpilih";
            display.classList.add('text-amber-600');
        }
    }

    async function deleteFile(fileId, button) {
        if (!confirm('Apakah Anda yakin ingin menghapus file ini?')) return;

        try {
            const response = await axios.delete('<?= base_url('documentation/file') ?>/' + fileId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            });

            if (response.data.status === 'success') {
                const fileItem = button.closest('.flex.items-center.justify-between');
                fileItem.style.opacity = '0';
                setTimeout(() => fileItem.remove(), 300);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'File telah dihapus',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error.response?.data?.message || 'Gagal menghapus file'
            });
        }
    }
</script>

<style>
    @keyframes pulse-subtle {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.95;
            transform: scale(0.998);
        }
    }

    .animate-pulse-subtle {
        animation: pulse-subtle 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>

<?= $this->endSection() ?>