<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-1 text-sm text-slate-500">Verifikasi berkas untuk: <span class="font-bold text-slate-800"><?= esc($travelRequest->no_surat_tugas) ?></span></p>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" onclick="verifyAll()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md transition-all">
            <i data-lucide="check-check" class="w-4 h-4"></i>
            Verifikasi Semua
        </button>
        <button type="button" onclick="rejectAll()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-md transition-all">
            <i data-lucide="x-circle" class="w-4 h-4"></i>
            Tolak Semua
        </button>
        <a href="<?= base_url('travel/active') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-200px)] min-h-[600px]">
    <!-- Left Sidebar: Checklist Items -->
    <div class="lg:col-span-4 flex flex-col gap-4 overflow-hidden">
        <!-- Narrative Report (Phase 27) -->
        <?php if (!empty($travelRequest->report_narrative)): ?>
            <div class="card p-4 border-l-4 border-l-blue-500 bg-blue-50/30">
                <h3 class="font-bold text-slate-800 text-[10px] uppercase tracking-wider mb-2 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-3 h-3 text-blue-500"></i>
                    Narasi Laporan / Catatan Dosen
                </h3>
                <div class="text-[11px] text-slate-600 italic leading-relaxed line-clamp-4 hover:line-clamp-none transition-all cursor-pointer" title="Klik untuk lihat selengkapnya">
                    <?= nl2br(esc((string) $travelRequest->report_narrative)) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card flex flex-col h-full border-t-4 border-t-primary-500 max-h-[calc(100vh-350px)]">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Checklist Berkas</h3>
                <span class="text-[10px] font-black bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full">
                    <?= count($completeness) ?> ITEM
                </span>
            </div>

            <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar">
                <?php foreach ($completeness as $item): ?>
                    <button type="button"
                        onclick="selectItem(<?= $item->id ?>, '<?= esc(addslashes($item->item_name)) ?>', '<?= $item->status ?>', '<?= esc(addslashes($item->verification_note ?? '')) ?>')"
                        id="item-btn-<?= $item->id ?>"
                        class="item-row w-full text-left p-3 rounded-xl border transition-all hover:bg-white hover:shadow-md group relative <?= (!empty($item->files)) ? 'border-primary-100 bg-primary-50/30' : 'border-slate-100 bg-white opacity-60' ?>">

                        <div class="flex items-start gap-3">
                            <div class="mt-1">
                                <?php if ($item->status === 'verified'): ?>
                                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </div>
                                <?php elseif ($item->status === 'rejected'): ?>
                                    <div class="w-5 h-5 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </div>
                                <?php elseif (!empty($item->files)): ?>
                                    <div class="w-5 h-5 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center animate-pulse">
                                        <i data-lucide="file-up" class="w-3 h-3"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-5 h-5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <i data-lucide="minus" class="w-3 h-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-800 text-xs truncate mb-0.5"><?= esc($item->item_name) ?></h4>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] uppercase font-black text-slate-400"><?= count($item->files) ?> File</span>
                                    <?php if ($item->status === 'verified'): ?>
                                        <span class="text-[9px] font-bold text-emerald-600">Terverifikasi</span>
                                    <?php elseif ($item->status === 'rejected'): ?>
                                        <span class="text-[9px] font-bold text-red-600">Ditolak</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-primary-500 transition-colors"></i>
                        </div>

                        <!-- Mini preview dots if has files -->
                        <?php if (!empty($item->files)): ?>
                            <div class="flex gap-1 mt-2 ml-8">
                                <?php foreach (array_slice($item->files, 0, 5) as $f): ?>
                                    <div class="w-1.5 h-1.5 rounded-full bg-primary-300"></div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Hidden data for JS -->
                            <div id="item-files-<?= $item->id ?>" class="hidden">
                                <?= json_encode($item->files) ?>
                            </div>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right Content: Preview & Action -->
    <div class="lg:col-span-8 flex flex-col gap-4 overflow-hidden">
        <div id="empty-state" class="card h-full flex flex-col items-center justify-center text-center p-12 bg-slate-50/50 border-2 border-dashed border-slate-200">
            <div class="w-20 h-20 rounded-full bg-white shadow-sm flex items-center justify-center mb-4">
                <i data-lucide="eye" class="w-10 h-10 text-slate-300"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-700">Pilih Item untuk Verifikasi</h3>
            <p class="text-slate-500 max-w-xs mt-2 text-sm leading-relaxed">Klik salah satu item checklist di sebelah kiri untuk melihat dokumen yang telah diunggah.</p>
        </div>

        <div id="preview-container" class="hidden h-full">
            <div class="card flex flex-col h-full overflow-hidden">
                <!-- Preview Header -->
                <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white z-10">
                    <div>
                        <h3 id="preview-item-name" class="font-black text-slate-800 text-sm uppercase tracking-tight"></h3>
                        <p id="preview-file-count" class="text-[10px] text-slate-400 font-bold uppercase"></p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a id="download-btn" href="#" target="_blank" class="btn-secondary py-1.5 px-3 text-xs shadow-sm items-center gap-2 hidden">
                            <i data-lucide="download" class="w-4 h-4 text-emerald-500"></i>
                            Download
                        </a>
                        <button type="button" onclick="openVerifyModal()" class="btn-primary py-1.5 px-4 text-xs shadow-md">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Verifikasi
                        </button>
                        <button type="button" onclick="openRejectModal()" class="btn-danger py-1.5 px-4 text-xs shadow-md">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Tolak
                        </button>
                    </div>
                </div>

                <!-- File Selection Bar (if multiple) -->
                <div id="file-tabs" class="flex items-center gap-2 p-2 bg-slate-50 border-b border-slate-100 overflow-x-auto custom-scrollbar">
                    <!-- Tabs will be injected here -->
                </div>

                <!-- Main Preview Area -->
                <div id="preview-pane" class="flex-1 bg-slate-800 relative overflow-hidden flex items-center justify-center">
                    <div id="preview-loading" class="absolute inset-0 z-20 bg-slate-800 hidden">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-10 h-10 border-4 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-widest">Memuat Dokumen...</span>
                        </div>
                    </div>

                    <iframe id="pdf-viewer" class="w-full h-full border-none hidden" src=""></iframe>
                    <img id="image-viewer" class="max-w-full max-h-full object-contain hidden shadow-2xl" src="" alt="Preview">

                    <div id="no-preview" class="hidden flex-col items-center gap-4 text-white p-12 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-white/10 flex items-center justify-center mb-2">
                            <i data-lucide="file-warning" class="w-8 h-8 text-amber-400"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-sm">Preview Tidak Tersedia</h4>
                            <p class="text-xs text-white/50">File ini tidak mendukung pratinjau langsung.</p>
                        </div>
                        <a id="download-fallback" href="#" class="btn-primary px-6 py-2 text-xs font-black uppercase tracking-wider">
                            Unduh Dokumen
                        </a>
                    </div>

                    <button type="button" id="expand-btn" onclick="expandPreview()" class="absolute bottom-6 right-6 w-12 h-12 rounded-full bg-primary-500 text-white shadow-xl hover:scale-110 active:scale-95 transition-all flex items-center justify-center z-30">
                        <i data-lucide="maximize-2" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox / Fullscreen Modal -->
<div id="fullscreen-overlay" class="fixed inset-0 z-100 bg-black/95 p-4 hidden">
    <button onclick="closeFullscreen()" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors">
        <i data-lucide="x" class="w-10 h-10"></i>
    </button>
    <div id="fullscreen-content" class="w-full h-full flex items-center justify-center">
        <!-- Content injected here -->
    </div>
</div>

<!-- Verify/Reject Modal (Reusing SweetAlert or Small Custom Modal) -->
<template id="reject-form">
    <div class="text-left py-4">
        <label class="block text-sm font-bold text-slate-700 mb-2">Alasan Penolakan</label>
        <textarea id="reject-note" class="w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500 text-sm" rows="4" placeholder="Contoh: Lampiran nota tidak jelas atau tidak sesuai nominal..."></textarea>
    </div>
</template>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .item-row.active {
        border-color: #6366f1;
        /* primary-500 equivalent */
        background-color: #ffffff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        /* shadow-lg */
        z-index: 10;
        transform: scale(1.02);
    }

    .file-tab.active {
        background-color: #ffffff;
        border-color: #e0e7ff;
        /* primary-200 */
        color: #4f46e5;
        /* primary-600 */
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        /* shadow-sm */
        font-weight: 700;
    }

    .file-tab {
        padding: 0.375rem 0.75rem;
        /* px-3 py-1.5 */
        border-radius: 0.5rem;
        /* rounded-lg */
        border: 1px solid transparent;
        color: #64748b;
        /* slate-500 */
        font-size: 10px;
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        /* 1.5 equivalent */
    }

    .file-tab:hover {
        background-color: #ffffff;
        border-color: #e2e8f0;
        /* slate-200 */
    }
</style>

<script>
    let currentItem = null;
    let currentFiles = [];
    let activeFileIndex = 0;

    function selectItem(id, name, status, note) {
        // UI Update
        document.querySelectorAll('.item-row').forEach(el => el.classList.remove('active'));
        document.getElementById('item-btn-' + id).classList.add('active');

        document.getElementById('empty-state').classList.add('hidden');
        const container = document.getElementById('preview-container');
        container.classList.remove('hidden');
        container.classList.add('flex', 'flex-col');

        // Data Update
        currentItem = {
            id,
            name,
            status,
            note
        };
        const filesJson = document.getElementById('item-files-' + id);
        currentFiles = filesJson ? JSON.parse(filesJson.innerText) : [];

        // Header
        document.getElementById('preview-item-name').innerText = name;
        document.getElementById('preview-file-count').innerText = currentFiles.length + ' File Terlampir';

        // Tabs
        renderTabs();

        // Initial File
        if (currentFiles.length > 0) {
            showFile(0);
        } else {
            showNoFiles();
        }
    }

    function renderTabs() {
        const bar = document.getElementById('file-tabs');
        bar.innerHTML = '';

        currentFiles.forEach((file, idx) => {
            const tab = document.createElement('button');
            tab.className = `file-tab ${idx === activeFileIndex ? 'active' : ''}`;
            tab.onclick = () => showFile(idx);

            const isPdf = file.file_path.toLowerCase().endsWith('.pdf');
            const isWord = file.file_path.toLowerCase().endsWith('.docx');
            const icon = isPdf ? 'file-text' : (isWord ? 'file-edit' : 'image');

            tab.innerHTML = `
            <i data-lucide="${icon}" class="w-3.5 h-3.5 ${isPdf ? 'text-red-500' : (isWord ? 'text-primary-500' : 'text-blue-500')}"></i>
            <span>${file.original_name}</span>
        `;
            bar.appendChild(tab);
        });
        lucide.createIcons();
    }

    function showFile(index) {
        activeFileIndex = index;
        const file = currentFiles[index];
        const url = '<?= base_url('documentation/file') ?>/' + file.id; // Using view-file route for inline preview

        // Update tabs active state
        document.querySelectorAll('.file-tab').forEach((t, i) => {
            if (i === index) t.classList.add('active');
            else t.classList.remove('active');
        });

        const isPdf = file.file_path.toLowerCase().endsWith('.pdf');
        const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(file.file_path);

        const loader = document.getElementById('preview-loading');
        const pdfViewer = document.getElementById('pdf-viewer');
        const imgViewer = document.getElementById('image-viewer');
        const expandBtn = document.getElementById('expand-btn');
        const noPreview = document.getElementById('no-preview');
        const downloadBtn = document.getElementById('download-btn');
        const downloadFallback = document.getElementById('download-fallback');

        loader.classList.remove('hidden');
        loader.classList.add('flex', 'items-center', 'justify-center');
        pdfViewer.classList.add('hidden');
        imgViewer.classList.add('hidden');
        expandBtn.classList.add('hidden');
        noPreview.classList.add('hidden');

        // Update download links (use original download route)
        const downloadUrl = '<?= base_url('documentation/download') ?>/' + file.id;
        downloadBtn.href = downloadUrl;
        downloadBtn.classList.remove('hidden');
        downloadFallback.href = downloadUrl;

        if (isPdf) {
            pdfViewer.src = url;
            pdfViewer.onload = () => {
                loader.classList.add('hidden');
                pdfViewer.classList.remove('hidden');
                expandBtn.classList.remove('hidden');
            };
        } else if (isImg) {
            imgViewer.src = url;
            imgViewer.onload = () => {
                loader.classList.add('hidden');
                imgViewer.classList.remove('hidden');
                expandBtn.classList.remove('hidden');
            };
        } else {
            // Fallback for DOCX or others
            loader.classList.add('hidden');
            noPreview.classList.remove('hidden');
            noPreview.classList.add('flex');
            lucide.createIcons();
        }
    }

    function showNoFiles() {
        document.getElementById('preview-pane').innerHTML = `
        <div class="flex flex-col items-center text-slate-500">
            <i data-lucide="file-warning" class="w-12 h-12 mb-2 opacity-20"></i>
            <span class="text-xs font-bold uppercase">Tidak ada file yang diunggah</span>
        </div>
    `;
        lucide.createIcons();
    }

    function expandPreview() {
        const overlay = document.getElementById('fullscreen-overlay');
        const content = document.getElementById('fullscreen-content');
        const file = currentFiles[activeFileIndex];
        const url = '<?= base_url('documentation/file') ?>/' + file.id;

        overlay.classList.remove('hidden');
        overlay.classList.add('flex', 'items-center', 'justify-center');

        const isPdf = file.file_path.toLowerCase().endsWith('.pdf');
        if (isPdf) {
            content.innerHTML = `<iframe src="${url}" class="w-full h-full border-none"></iframe>`;
        } else {
            content.innerHTML = `<img src="${url}" class="max-w-full max-h-full object-contain">`;
        }
    }

    function closeFullscreen() {
        const overlay = document.getElementById('fullscreen-overlay');
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    }

    async function openVerifyModal() {
        const result = await Swal.fire({
            title: 'Verifikasi Berkas?',
            text: 'Apakah Anda yakin berkas "' + currentItem.name + '" sudah sesuai?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Verifikasi',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            submitVerification('verified', '');
        }
    }

    async function openRejectModal() {
        const template = document.getElementById('reject-form').innerHTML;

        const {
            value: note,
            isConfirmed
        } = await Swal.fire({
            title: 'Tolak Berkas',
            html: template,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Tolak Sekarang',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const noteVal = document.getElementById('reject-note').value;
                if (!noteVal) {
                    Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                }
                return noteVal;
            }
        });

        if (isConfirmed) {
            submitVerification('rejected', note);
        }
    }

    async function submitVerification(status, note) {
        try {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const response = await axios.post('<?= base_url('travel/completeness') ?>/' + currentItem.id + '/verify', {
                status: status,
                verification_note: note
            }, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            });

            if (response.data.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Item berhasil ' + (status === 'verified' ? 'diverifikasi' : 'ditolak'),
                    timer: 1500
                });
                window.location.reload();
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error.response?.data?.message || 'Gagal menyimpan verifikasi'
            });
        }
    }

    async function verifyAll() {
        const result = await Swal.fire({
            title: 'Verifikasi Semua Berkas?',
            text: 'Tindakan ini akan memverifikasi seluruh item yang ada sekaligus. Lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Verifikasi Semua',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const response = await axios.post('<?= base_url('travel/completeness/' . $travelRequest->id . '/verify-all') ?>', {}, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                });

                if (response.data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Seluruh item berhasil diverifikasi',
                        timer: 1500
                    });
                    window.location.reload();
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.response?.data?.message || 'Gagal melakukan verifikasi massal'
                });
            }
        }
    }

    async function rejectAll() {
        const template = document.getElementById('reject-form').innerHTML;

        const {
            value: note,
            isConfirmed
        } = await Swal.fire({
            title: 'Tolak Semua Berkas',
            html: template,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Tolak Sekarang',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const noteVal = document.getElementById('reject-note').value;
                if (!noteVal) {
                    Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                }
                return noteVal;
            }
        });

        if (isConfirmed) {
            try {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const response = await axios.post('<?= base_url('travel/completeness/' . $travelRequest->id . '/reject-all') ?>', {
                    verification_note: note
                }, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                });

                if (response.data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Seluruh item berhasil ditolak',
                        timer: 1500
                    });
                    window.location.reload();
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.response?.data?.message || 'Gagal melakukan penolakan massal'
                });
            }
        }
    }
</script>

<?= $this->endSection() ?>