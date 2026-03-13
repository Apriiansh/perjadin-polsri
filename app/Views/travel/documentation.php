<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-1 text-sm text-slate-500">Lengkapi berkas dokumentasi untuk Perjalanan Dinas: <span class="font-bold text-slate-800"><?= esc($travelRequest->no_surat_tugas) ?></span></p>
    </div>

    <div class="flex items-center gap-2">
        <a href="<?= base_url('travel') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>
</div>

<form action="<?= base_url('documentation/' . $travelRequest->id) ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side: Checklist Form -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6 border-t-4 border-t-amber-500">
                <div class="flex items-center gap-2 mb-6 pb-2 border-b border-slate-100">
                    <i data-lucide="list-checks" class="w-5 h-5 text-amber-500"></i>
                    <h3 class="font-bold text-slate-800 uppercase tracking-wider text-sm">Daftar Kelengkapan Berkas</h3>
                </div>

                <div class="space-y-8">
                    <?php foreach ($completeness as $item): ?>
                        <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/30 hover:bg-white hover:shadow-md transition-all">
                            <div class="flex flex-col md:flex-row justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <!-- <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-[10px]">
                                            <?= $item->id ?>
                                        </span> -->
                                        <h4 class="font-black text-slate-800 text-sm"><?= esc($item->item_name) ?></h4>
                                    </div>
                                    <p class="text-[11px] text-slate-500 italic"><?= esc($item->remark ?: 'Unggah semua bukti fisik pendukung (Nota, Tiket, dll)') ?></p>

                                    <!-- Existing Files -->
                                    <?php if (!empty($item->files)): ?>
                                        <div class="mt-4 ml-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <?php foreach ($item->files as $file): ?>
                                                <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-100 shadow-sm group/file">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <i data-lucide="file-text" class="w-3 h-3 text-blue-500"></i>
                                                        <span class="text-[10px] text-slate-600 truncate max-w-[120px]" title="<?= esc($file->original_name) ?>">
                                                            <?= esc($file->original_name) ?>
                                                        </span>
                                                    </div>
                                                    <button type="button"
                                                        onclick="deleteFile(<?= $file->id ?>, this)"
                                                        class="opacity-0 group-hover/file:opacity-100 transition-opacity p-1 text-red-500 hover:bg-red-50 rounded">
                                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="w-full md:w-64 shrink-0">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Unggah File</label>
                                    <div class="relative group">
                                        <input type="file"
                                            name="documents_<?= $item->id ?>[]"
                                            id="file_<?= $item->id ?>"
                                            multiple
                                            accept=".pdf,.docx,image/*"
                                            class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer"
                                            onchange="updateFileNameDisplay(this, 'display_<?= $item->id ?>')">
                                        <div class="w-full border-2 border-dashed border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center gap-2 group-hover:border-amber-400 group-hover:bg-amber-50/50 transition-all bg-white shadow-sm">
                                            <i data-lucide="upload-cloud" class="w-6 h-6 text-slate-300 group-hover:text-amber-500 transition-colors"></i>
                                            <span id="display_<?= $item->id ?>" class="text-[10px] font-bold text-slate-500 text-center line-clamp-1 truncate w-full px-2">Klik atau Drop File</span>
                                        </div>
                                    </div>
                                </div>
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

                <button type="submit" class="btn-primary w-full py-3 shadow-lg hover:shadow-primary-200/50 transition-all flex items-center justify-center gap-3">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    <span class="uppercase font-black tracking-widest text-xs">Simpan Semua</span>
                </button>

                <p class="mt-4 text-[10px] text-center text-slate-400 italic">Pastikan seluruh berkas sudah benar sebelum dikirim untuk verifikasi.</p>
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

<?= $this->endSection() ?>