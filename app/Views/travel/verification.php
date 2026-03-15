<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- =========================================================
     PAGE HEADER
     ========================================================= -->
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Verifikasi Berkas</span>
        </div>
        <h1 class="text-xl font-extrabold text-slate-900 leading-tight"><?= esc($title) ?></h1>
        <p class="mt-0.5 text-xs text-slate-500">
            No. Surat Tugas:
            <span class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md"><?= esc($travelRequest->no_surat_tugas) ?></span>
        </p>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <button type="button" onclick="verifyAll()"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow transition-all">
            <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
            Approve Semua
        </button>
        <button type="button" onclick="rejectAll()"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold shadow transition-all">
            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
            Reject Semua
        </button>
        <a href="<?= base_url('travel/active') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>

    </div>
</div>

<!-- =========================================================
     MAIN LAYOUT — 3 COLUMNS
     ========================================================= -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5" style="height: calc(100vh - 175px); min-height: 580px;">

    <!-- =====================================================
         COL 1 — MEMBER LIST
         ===================================================== -->
    <div class="lg:col-span-3 flex flex-col overflow-hidden">
        <div class="card flex flex-col h-full border-t-4 border-t-primary-500 overflow-hidden">

            <div class="px-4 border-b border-slate-100 shrink-0">

                <!-- Summary Stats -->
                <?php
                $totalVerified = 0;
                $totalRejected = 0;
                $totalPending = 0;
                foreach ($members as $m) {
                    $allV = true;
                    $anyR = false;
                    foreach ($m->completeness as $c) {
                        if ($c->status !== 'verified') $allV = false;
                        if ($c->status === 'rejected') $anyR = true;
                    }
                    if ($allV && count($m->completeness) > 0) $totalVerified++;
                    elseif ($anyR) $totalRejected++;
                    else $totalPending++;
                }
                ?>
                <div class="grid grid-cols-3 gap-1.5 text-center">
                    <div class="rounded-md bg-emerald-50 py-1.5">
                        <p class="text-sm font-black text-emerald-600"><?= $totalVerified ?></p>
                        <p class="text-[8px] text-emerald-500 font-bold uppercase">Approved</p>
                    </div>
                    <div class="rounded-md bg-amber-50 py-1.5">
                        <p class="text-sm font-black text-amber-600"><?= $totalPending ?></p>
                        <p class="text-[8px] text-amber-500 font-bold uppercase">Pending</p>
                    </div>
                    <div class="rounded-md bg-rose-50 py-1.5">
                        <p class="text-sm font-black text-rose-600"><?= $totalRejected ?></p>
                        <p class="text-[8px] text-rose-500 font-bold uppercase">Rejected</p>
                    </div>
                </div>
                <h3 class="text-xs mt-5 font-black text-slate-500 uppercase tracking-widest mb-2.5">Daftar Anggota</h3>
            </div>

            <!-- Member Items -->
            <div class="flex-1 overflow-y-auto p-2.5 space-y-1 custom-scrollbar">
                <?php foreach ($members as $member): ?>
                    <?php
                    $totalItems    = count($member->completeness);
                    $verifiedCount = 0;
                    $hasRejected   = false;
                    $uploadedCount = 0;
                    foreach ($member->completeness as $item) {
                        if ($item->status === 'verified') $verifiedCount++;
                        if ($item->status === 'rejected') $hasRejected = true;
                        if (!empty($item->files)) $uploadedCount++;
                    }
                    $pct = $totalItems > 0 ? round(($verifiedCount / $totalItems) * 100) : 0;

                    if ($verifiedCount === $totalItems && $totalItems > 0) {
                        $sc = 'emerald';
                        $sl = 'Approved';
                    } elseif ($hasRejected) {
                        $sc = 'rose';
                        $sl = 'Rejected';
                    } elseif ($uploadedCount > 0) {
                        $sc = 'amber';
                        $sl = 'Pending';
                    } else {
                        $sc = 'slate';
                        $sl = 'Menunggu';
                    }
                    ?>
                    <button type="button"
                        id="member-btn-<?= $member->id ?>"
                        onclick="selectMember(<?= $member->id ?>, <?= htmlspecialchars(json_encode($member)) ?>)"
                        class="member-row w-full text-left p-3 rounded-lg border border-slate-100 bg-slate-50/50 transition-all hover:bg-white hover:shadow-sm group">

                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-md bg-<?= $sc ?>-100 text-<?= $sc ?>-600 flex items-center justify-center font-black text-sm shrink-0">
                                <?= strtoupper(substr($member->employee_name, 0, 2)) ?>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <h4 class="font-black text-slate-800 text-[11px] truncate leading-tight"><?= esc($member->employee_name) ?></h4>
                                    <!-- Member-level status badge only -->
                                    <span class="shrink-0 text-[8px] font-black px-1.5 py-0.5 rounded bg-<?= $sc ?>-100 text-<?= $sc ?>-600">
                                        <?= $sl ?>
                                    </span>
                                </div>
                                <p class="text-[9px] text-slate-400 font-medium"><?= esc($member->nip) ?></p>

                                <!-- Progress bar
                                <div class="mt-1.5 flex items-center gap-1.5">
                                    <div class="flex-1 h-1 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-<?= $sc ?>-400 rounded-full" style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="text-[8px] font-bold text-slate-400"><?= $verifiedCount ?>/<?= $totalItems ?></span>
                                </div> -->
                            </div>

                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 group-hover:text-primary-500 transition-colors shrink-0"></i>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- =====================================================
         COL 2 — MEMBER DETAIL + DOCS
         ===================================================== -->
    <div class="lg:col-span-5 flex flex-col gap-3 overflow-hidden">

        <!-- Empty State -->
        <div id="empty-state" class="card h-full flex flex-col items-center justify-center text-center p-12 bg-slate-50/50 border-2 border-dashed border-slate-200">
            <div class="w-16 h-16 rounded-lg bg-slate-100 flex items-center justify-center mb-4">
                <i data-lucide="users" class="w-8 h-8 text-slate-300"></i>
            </div>
            <h3 class="text-sm font-black text-slate-600">Pilih Anggota</h3>
            <p class="text-slate-400 max-w-xs mt-1.5 text-xs leading-relaxed">
                Klik salah satu anggota di panel kiri untuk meninjau berkas laporan perjalanan dinas mereka.
            </p>
        </div>

        <!-- Member Detail (hidden by default) -->
        <div id="member-detail-container" class="hidden flex-col gap-3 h-full overflow-hidden">

            <!-- Member Header -->
            <div class="card p-4 border-l-4 border-l-primary-500 shrink-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h2 id="detail-member-name" class="text-base font-black text-slate-900 leading-tight truncate"></h2>
                            <!-- Member-level status — updated by JS -->
                            <span id="detail-member-status" class="shrink-0 text-[9px] font-black px-2 py-0.5 rounded-md"></span>
                        </div>
                        <p id="detail-member-nip" class="text-[10px] text-slate-400 font-medium uppercase mt-0.5"></p>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" onclick="verifyMember()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-[10px] font-black shadow transition-all">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            Approve
                        </button>
                        <button type="button" onclick="rejectMember()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md text-[10px] font-black shadow transition-all">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                            Reject
                        </button>
                    </div>
                </div>

                <!-- Narrative (conditional) -->
                <div id="detail-narrative-container" class="hidden mt-3 pt-3 border-t border-slate-100">
                    <p class="text-[9px] uppercase font-black text-slate-400 tracking-widest mb-1.5 flex items-center gap-1">
                        <i data-lucide="message-square" class="w-3 h-3"></i>
                        Narasi Laporan
                    </p>
                    <div class="p-3 bg-blue-50 rounded-md border border-blue-100 relative">
                        <i data-lucide="quote" class="w-3 h-3 text-blue-200 absolute top-2 left-2"></i>
                        <p id="detail-narrative-text" class="text-[11px] text-slate-600 italic leading-relaxed pl-4 whitespace-pre-line"></p>
                    </div>
                </div>

                <!-- Rejection Note (conditional) -->
                <div id="detail-rejection-container" class="hidden mt-3 pt-3 border-t border-slate-100">
                    <p class="text-[9px] uppercase font-black text-red-500 tracking-widest mb-1.5 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        Catatan Penolakan
                    </p>
                    <div class="p-3 bg-red-50 rounded-md border border-red-100 relative">
                        <i data-lucide="info" class="w-3 h-3 text-red-200 absolute top-2 left-2"></i>
                        <p id="detail-rejection-text" class="text-[11px] text-red-700 font-bold leading-relaxed pl-4"></p>
                    </div>
                </div>
            </div>

            <!-- Documentation Grid -->
            <div class="card flex flex-col flex-1 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 shrink-0 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">Berkas Dokumentasi</h3>
                    <span id="doc-progress-label" class="text-[10px] font-semibold text-slate-400"></span>
                </div>
                <div id="documentation-grid"
                    class="flex-1 overflow-y-auto p-3 grid grid-cols-1 sm:grid-cols-2 gap-2 content-start custom-scrollbar">
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         COL 3 — PREVIEW PANE
         ===================================================== -->
    <div class="lg:col-span-4 flex flex-col overflow-hidden">
        <div class="card flex flex-col h-full overflow-hidden bg-slate-900 border-none shadow-2xl relative">

            <!-- Preview Header -->
            <div id="preview-header"
                class="absolute top-0 left-0 right-0 z-30 px-3 py-2.5 bg-slate-900/80 backdrop-blur-sm border-b border-white/10 flex items-center justify-between gap-2 transition-all opacity-0 pointer-events-none">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-5 h-5 rounded bg-white/10 flex items-center justify-center shrink-0">
                        <i id="preview-type-icon" data-lucide="file" class="w-3 h-3 text-white/60"></i>
                    </div>
                    <h4 id="preview-filename" class="text-[10px] font-semibold text-white/80 truncate"></h4>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <a id="preview-download" href="#"
                        class="p-1.5 rounded bg-white/10 hover:bg-white/20 text-white/70 hover:text-white transition-all" title="Unduh">
                        <i data-lucide="download" class="w-3 h-3"></i>
                    </a>
                    <button onclick="expandPreview()"
                        class="p-1.5 rounded bg-primary-500/80 hover:bg-primary-500 text-white transition-all" title="Layar Penuh">
                        <i data-lucide="maximize-2" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>

            <!-- Placeholder -->
            <div id="preview-placeholder" class="flex-1 flex flex-col items-center justify-center text-center px-8 py-12">
                <div class="w-14 h-14 rounded-lg bg-white/5 flex items-center justify-center mb-4">
                    <i data-lucide="scan-eye" class="w-7 h-7 text-slate-600"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-500">Pratinjau Dokumen</h4>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed max-w-[180px]">
                    Klik thumbnail berkas di panel tengah untuk melihat isi dokumen.
                </p>
            </div>

            <!-- Active Preview -->
            <div id="preview-pane" class="flex-1 hidden relative items-center justify-center overflow-hidden pt-10">
                <div id="preview-loading" class="absolute inset-0 z-20 bg-slate-900 items-center justify-center hidden">
                    <div class="w-7 h-7 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
                <iframe id="pdf-viewer" class="w-full h-full border-none hidden" src=""></iframe>
                <img id="image-viewer" class="max-w-full max-h-full object-contain hidden" src="" alt="Preview">
                <div id="no-preview" class="hidden flex-col items-center gap-3 text-white text-center p-8">
                    <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center">
                        <i data-lucide="file-warning" class="w-6 h-6 text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white/60 mb-0.5">Format tidak mendukung pratinjau.</p>
                        <p class="text-[10px] text-white/30">Unduh berkas untuk membukanya.</p>
                    </div>
                    <a id="download-fallback" href="#"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-md text-xs font-black transition-all">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        Unduh Berkas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Lightbox -->
<div id="fullscreen-overlay" class="fixed inset-0 z-100 bg-black/95 hidden">
    <button onclick="closeFullscreen()"
        class="absolute top-5 right-5 z-10 text-white/40 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10">
        <i data-lucide="x" class="w-7 h-7"></i>
    </button>
    <div id="fullscreen-content" class="w-full h-full flex items-center justify-center p-6"></div>
</div>

<!-- Reject Form Template -->
<template id="reject-form">
    <div class="text-left py-3">
        <label class="block text-sm font-bold text-slate-700 mb-1.5">
            Alasan Penolakan <span class="text-red-500">*</span>
        </label>
        <textarea id="reject-note" rows="2"
            class="w-full p-4 rounded-md border-slate-200 focus:border-red-500 focus:ring-red-500 text-sm resize-none"
            placeholder="Tulis alasan penolakan yang jelas..."></textarea>
        <p class="mt-1.5 text-[10px] text-slate-400">Alasan ini akan terlihat oleh anggota yang bersangkutan.</p>
    </div>
</template>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .member-row.active {
        border-color: #6366f1;
        background-color: #fff;
        box-shadow: 0 2px 12px -2px rgba(99, 102, 241, 0.2);
        transform: translateX(3px);
    }

    .file-thumb.active {
        background-color: #4f46e5 !important;
        color: #fff !important;
        border-color: #4338ca !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25);
    }

    @keyframes pulse-subtle {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.92;
            transform: scale(0.995);
        }
    }

    .animate-pulse-subtle {
        animation: pulse-subtle 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .animate-in {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #member-detail-container {
        display: none;
    }

    #member-detail-container.flex {
        display: flex;
    }

    #preview-loading.flex {
        display: flex;
    }

    #no-preview.flex {
        display: flex;
    }
</style>

<script>
    let currentMember = null;
    let currentPreviewFile = null;

    /* ── Select Member ── */
    function selectMember(id, memberData) {
        currentMember = memberData;

        document.querySelectorAll('.member-row').forEach(el => el.classList.remove('active'));
        document.getElementById('member-btn-' + id).classList.add('active');

        document.getElementById('empty-state').classList.add('hidden');
        const container = document.getElementById('member-detail-container');
        container.classList.remove('hidden');
        container.classList.add('flex');

        document.getElementById('detail-member-name').innerText = memberData.employee_name;
        document.getElementById('detail-member-nip').innerText = 'NIP: ' + memberData.nip;

        // Compute member-level status
        const items = memberData.completeness;
        const verifiedCount = items.filter(i => i.status === 'verified').length;
        const hasRejected = items.some(i => i.status === 'rejected');
        const hasUploaded = items.some(i => i.files && i.files.length > 0);

        let statusLabel, statusClass;
        if (verifiedCount === items.length && items.length > 0) {
            statusLabel = 'Approved';
            statusClass = 'bg-emerald-100 text-emerald-700';
        } else if (hasRejected) {
            statusLabel = 'Rejected';
            statusClass = 'bg-red-100 text-red-700';
        } else if (hasUploaded) {
            statusLabel = 'Pending';
            statusClass = 'bg-amber-100 text-amber-700';
        } else {
            statusLabel = 'Menunggu';
            statusClass = 'bg-slate-100 text-slate-500';
        }
        const statusEl = document.getElementById('detail-member-status');
        statusEl.innerText = statusLabel;
        statusEl.className = `shrink-0 text-[9px] font-black px-2 py-0.5 rounded-md ${statusClass}`;

        // Narrative
        const narrativeEl = document.getElementById('detail-narrative-container');
        if (memberData.report_narrative) {
            narrativeEl.classList.remove('hidden');
            document.getElementById('detail-narrative-text').innerText = memberData.report_narrative;
        } else {
            narrativeEl.classList.add('hidden');
        }

        // Rejection Note
        const rejectionEl = document.getElementById('detail-rejection-container');
        const rejectedItemWithNote = items.find(i => i.status === 'rejected' && i.verification_note);
        if (rejectedItemWithNote) {
            rejectionEl.classList.remove('hidden');
            document.getElementById('detail-rejection-text').innerText = rejectedItemWithNote.verification_note;
        } else {
            rejectionEl.classList.add('hidden');
        }

        renderDocumentationGrid(items);
        resetPreview();
    }

    /* ── Reset Preview ── */
    function resetPreview() {
        currentPreviewFile = null;
        document.getElementById('preview-pane').classList.add('hidden');
        document.getElementById('preview-placeholder').classList.remove('hidden');
        document.getElementById('preview-header').classList.add('opacity-0', 'pointer-events-none');
        document.querySelectorAll('.file-thumb').forEach(t => t.classList.remove('active'));
    }

    /* ── Render Documentation Grid ──
       Items are neutral containers — no per-item status label.
       Approved / Rejected verdict is shown at member level only.
    ── */
    function renderDocumentationGrid(items) {
        const grid = document.getElementById('documentation-grid');
        grid.innerHTML = '';

        items.forEach(item => {
            const hasFiles = item.files && item.files.length > 0;

            const card = document.createElement('div');
            card.className = `p-3 rounded-lg border ${item.status === 'rejected' ? 'border-red-200 bg-red-50/30' : (hasFiles ? 'border-slate-200 bg-white' : 'border-slate-100 bg-slate-50/50')} flex flex-col gap-2`;
            card.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <h5 class="text-[10px] font-black text-slate-600 uppercase tracking-tight leading-snug">${item.item_name}</h5>
                   </div>
                <div class="flex flex-wrap gap-1.5 items-center" id="file-list-${item.id}">
                    ${!hasFiles ? '<span class="text-[9px] text-slate-300 italic">Belum ada berkas</span>' : ''}
                </div>`;
            grid.appendChild(card);

            if (hasFiles) {
                const fileList = card.querySelector(`#file-list-${item.id}`);
                item.files.forEach((file, idx) => {
                    const ext = file.original_name.split('.').pop().toUpperCase();
                    const isPdf = ext === 'PDF';
                    const isImg = ['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP'].includes(ext);

                    const thumbCls = isPdf ?
                        'bg-red-50 border-red-100 text-red-500 hover:border-red-300' :
                        isImg ?
                        'bg-sky-50 border-sky-100 text-sky-500 hover:border-sky-300' :
                        'bg-slate-50 border-slate-200 text-slate-500 hover:border-slate-400';

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `file-thumb w-9 h-9 rounded-md border flex flex-col items-center justify-center transition-all ${thumbCls}`;
                    btn.title = file.original_name;
                    btn.innerHTML = `
                        <span class="text-[8px] font-black leading-none">${ext.slice(0, 3)}</span>
                        <span class="text-[7px] font-medium opacity-50 mt-0.5">${idx + 1}</span>
                    `;
                    btn.onclick = () => previewFile(file, btn);
                    fileList.appendChild(btn);
                });
            }
        });

        lucide.createIcons();
    }

    /* ── Preview File ── */
    function previewFile(file, btn) {
        currentPreviewFile = file;

        document.querySelectorAll('.file-thumb').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');

        document.getElementById('preview-placeholder').classList.add('hidden');
        const pane = document.getElementById('preview-pane');
        pane.classList.remove('hidden');
        pane.classList.add('flex');
        document.getElementById('preview-header').classList.remove('opacity-0', 'pointer-events-none');

        document.getElementById('preview-filename').innerText = file.original_name;
        document.getElementById('preview-download').href = '<?= base_url('documentation/download') ?>/' + file.id;
        document.getElementById('download-fallback').href = '<?= base_url('documentation/download') ?>/' + file.id;

        const url = '<?= base_url('documentation/file') ?>/' + file.id;
        const isPdf = file.file_path.toLowerCase().endsWith('.pdf');
        const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(file.file_path);

        const loader = document.getElementById('preview-loading');
        const pdfViewer = document.getElementById('pdf-viewer');
        const imgViewer = document.getElementById('image-viewer');
        const noPreview = document.getElementById('no-preview');
        const typeIcon = document.getElementById('preview-type-icon');

        loader.classList.remove('hidden');
        loader.classList.add('flex');
        pdfViewer.classList.add('hidden');
        imgViewer.classList.add('hidden');
        noPreview.classList.add('hidden');
        noPreview.classList.remove('flex');

        const hideLoader = () => {
            loader.classList.add('hidden');
            loader.classList.remove('flex');
        };

        if (isPdf) {
            typeIcon.setAttribute('data-lucide', 'file-text');
            pdfViewer.src = url;
            pdfViewer.onload = () => {
                hideLoader();
                pdfViewer.classList.remove('hidden');
            };
        } else if (isImg) {
            typeIcon.setAttribute('data-lucide', 'image');
            imgViewer.src = url;
            imgViewer.onload = () => {
                hideLoader();
                imgViewer.classList.remove('hidden');
            };
        } else {
            typeIcon.setAttribute('data-lucide', 'file');
            hideLoader();
            noPreview.classList.remove('hidden');
            noPreview.classList.add('flex');
        }

        lucide.createIcons();
    }

    /* ── Fullscreen ── */
    function expandPreview() {
        if (!currentPreviewFile) return;
        const url = '<?= base_url('documentation/file') ?>/' + currentPreviewFile.id;
        const isPdf = currentPreviewFile.file_path.toLowerCase().endsWith('.pdf');
        document.getElementById('fullscreen-overlay').classList.remove('hidden');
        document.getElementById('fullscreen-content').innerHTML = isPdf ?
            `<iframe src="${url}" class="w-full h-full border-none rounded-md"></iframe>` :
            `<img src="${url}" class="max-w-full max-h-full object-contain rounded-md shadow-2xl">`;
    }

    function closeFullscreen() {
        document.getElementById('fullscreen-overlay').classList.add('hidden');
        document.getElementById('fullscreen-content').innerHTML = '';
    }

    /* ── Verify Member ── */
    async function verifyMember() {
        const {
            isConfirmed
        } = await Swal.fire({
            title: 'Approve Anggota?',
            html: `<p class="text-sm text-slate-500">Semua berkas atas nama <strong class="text-slate-800">${currentMember.employee_name}</strong> akan disetujui.</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Ya, Approve',
            cancelButtonText: 'Batal'
        });
        if (isConfirmed) submitMemberVerification('verified');
    }

    /* ── Reject Member ── */
    async function rejectMember() {
        const {
            value: note,
            isConfirmed
        } = await Swal.fire({
            title: 'Reject Anggota?',
            html: document.getElementById('reject-form').innerHTML,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Reject',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const val = document.getElementById('reject-note').value.trim();
                if (!val) Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                return val;
            }
        });
        if (isConfirmed) submitMemberVerification('rejected', note);
    }

    /* ── Submit ── */
    async function submitMemberVerification(status, note = '') {
        try {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            const action = status === 'verified' ? 'verify' : 'reject';
            const response = await axios.post(
                `<?= base_url('travel/completeness/member') ?>/${currentMember.id}/${action}`, {
                    verification_note: note
                }, {
                    headers: {
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                }
            );
            if (response.data.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Status personil telah diperbarui.',
                    timer: 1400,
                    showConfirmButton: false
                });
                window.location.reload();
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error.response?.data?.message || 'Terjadi kesalahan sistem.'
            });
        }
    }

    /* ── Verify All ── */
    async function verifyAll() {
        const {
            isConfirmed
        } = await Swal.fire({
            title: 'Approve Semua Anggota?',
            text: 'Seluruh berkas dari semua anggota akan disetujui sekaligus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Ya, Approve Semua',
            cancelButtonText: 'Batal'
        });
        if (!isConfirmed) return;
        const res = await axios.post(
            '<?= base_url('travel/completeness/' . $travelRequest->id . '/verify-all') ?>', {}, {
                headers: {
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            }
        );
        if (res.data.status === 'success') window.location.reload();
    }

    /* ── Reject All ── */
    async function rejectAll() {
        const {
            value: note,
            isConfirmed
        } = await Swal.fire({
            title: 'Reject Semua Anggota?',
            input: 'textarea',
            inputPlaceholder: 'Tulis alasan penolakan massal...',
            inputAttributes: {
                rows: 4
            },
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Reject Semua',
            cancelButtonText: 'Batal',
            inputValidator: val => !val?.trim() ? 'Alasan penolakan wajib diisi!' : undefined
        });
        if (!isConfirmed) return;
        const res = await axios.post(
            '<?= base_url('travel/completeness/' . $travelRequest->id . '/reject-all') ?>', {
                verification_note: note
            }, {
                headers: {
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            }
        );
        if (res.data.status === 'success') window.location.reload();
    }
</script>

<?= $this->endSection() ?>