<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Verifikasi Berkas Mahasiswa</span>
            <?php
            // Calculate Global Status
            $hasRejected = false;
            $allVerified = true;
            foreach (($members ?? []) as $m) {
                foreach (($m->completeness ?? []) as $c) {
                    if ($c->status === 'rejected') $hasRejected = true;
                    if ($c->status !== 'verified') $allVerified = false;
                }
            }

            if ($request->status === 'completed' || $allVerified) {
                $lbl = 'Approved';
                $cls = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
            } elseif ($hasRejected) {
                $lbl = 'Needs Revision';
                $cls = 'bg-rose-50 text-rose-600 border border-rose-100';
            } else {
                $lbl = 'Reviewing';
                $cls = 'bg-amber-50 text-amber-600 border border-amber-100';
            }
            ?>
            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider <?= $cls ?>"><?= $lbl ?></span>
        </div>
        <h1 class="text-xl font-extrabold text-slate-900 leading-tight"><?= esc($title) ?></h1>
        <p class="mt-0.5 text-xs text-slate-500">
            No. Surat Tugas:
            <span class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md"><?= esc($request->no_surat_tugas) ?></span>
        </p>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <?php if ($request->status === 'active'): ?>
            <button type="button" onclick="verifyAll()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-bold shadow transition-all">
                <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                Approve Dokumentasi
            </button>
            <button type="button" onclick="rejectAll()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs font-bold shadow transition-all">
                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                Reject Dokumentasi
            </button>
        <?php else: ?>
            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-xs font-black">
                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                VERIFIKASI TERKUNCI
            </span>
        <?php endif; ?>
        <a href="<?= base_url('travel/student/' . $request->id) ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5" style="height: calc(100vh - 175px); min-height: 580px;">
    <?php
    $representativeName = '-';
    $representativeMember = null;
    foreach (($members ?? []) as $memberItem) {
        if ((int) ($memberItem->is_representative ?? 0) === 1) {
            $representativeName = $memberItem->name ?? '-';
            $representativeMember = $memberItem;
            break;
        }
    }
    if (!$representativeMember && !empty($members[0])) {
        $representativeMember = $members[0];
    }
    if ($representativeName === '-' && !empty($representativeMember->name)) {
        $representativeName = $representativeMember->name;
    }
    $completenessItems = $representativeMember->completeness ?? [];
    $reportNarrative = $representativeMember->report_narrative ?? null;
    ?>

    <!-- Left: Members List (ReadOnly Info) -->
    <div class="lg:col-span-3 flex flex-col overflow-hidden">
        <div class="card flex flex-col h-full border-t-4 border-t-slate-400 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest leading-none">Anggota Tim</h3>
                <span class="badge-secondary text-[9px]"><?= count($members) ?> Orang</span>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                <?php foreach ($members as $m): ?>
                    <div class="p-3 rounded-md border border-slate-100 bg-slate-50/30">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-sm bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase">
                                <?= substr($m->name, 0, 1) ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-slate-800 text-[11px] truncate leading-tight"><?= esc($m->name) ?></h4>
                                <p class="text-[9px] text-slate-400 font-mono"><?= esc($m->nim) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100">
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight mb-2">Unggahan Oleh:</p>
                <div class="flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-slate-400"></i>
                    <span class="text-xs font-black text-slate-900"><?= esc($representativeName) ?></span>
                </div>
                <p class="text-[9px] text-slate-400 mt-1 italic">Selaku Ketua Tim Mahasiswa</p>
            </div>
        </div>
    </div>

    <!-- Center: Documentation Content -->
    <div class="lg:col-span-5 flex flex-col gap-3 overflow-hidden">
        <div class="card flex flex-col h-full overflow-hidden border-t-4 border-t-blue-600">
            <!-- Narrative Report -->
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                    <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                    Narasi Laporan Perjalanan Dinas Mahasiswa
                </h3>
                <div class="p-4 bg-white rounded-md border border-slate-100 shadow-inner-sm italic text-xs text-slate-700 leading-relaxed">
                    <?= !empty($reportNarrative) ? nl2br(esc((string) $reportNarrative)) : '<span class="text-slate-300">Tidak ada narasi laporan.</span>' ?>
                </div>
            </div>

            <!-- Documents Grid -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                    <i data-lucide="files" class="w-3.5 h-3.5"></i>
                    Berkas Dokumentasi
                </h3>

                <div class="grid grid-cols-1 gap-3">
                    <?php if (empty($completenessItems)): ?>
                        <div class="p-4 rounded-md border border-dashed border-slate-200 bg-slate-50/50">
                            <p class="text-[10px] text-slate-400 italic">Belum ada item kelengkapan untuk diverifikasi.</p>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($completenessItems as $item): ?>
                        <div class="p-4 rounded-xl border <?= $item->status === 'rejected' ? 'border-rose-200 bg-rose-50/20' : ($item->status === 'verified' ? 'border-emerald-100 bg-emerald-50/10' : 'border-slate-100 bg-white') ?> shadow-sm transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="text-[10px] font-black text-slate-600 uppercase tracking-tight"><?= esc($item->item_name) ?></h5>
                                <?php
                                if ($item->status === 'verified') {
                                    $iLbl = 'Approved';
                                    $iCls = 'bg-emerald-50 text-emerald-600';
                                } elseif ($item->status === 'rejected') {
                                    $iLbl = 'Revision';
                                    $iCls = 'bg-rose-50 text-rose-600';
                                } else {
                                    $iLbl = 'Pending';
                                    $iCls = 'bg-amber-50 text-amber-600';
                                }
                                ?>
                                <span class="px-1.5 py-0.5 rounded text-[7px] font-black uppercase tracking-widest <?= $iCls ?> font-mono"><?= $iLbl ?></span>
                            </div>
                            
                            <?php if ($item->status === 'rejected' && !empty($item->verification_note)): ?>
                                <div class="mb-3 p-2 bg-white/60 border border-rose-100 rounded-lg text-[10px] text-rose-700 italic flex items-start gap-1.5">
                                    <i data-lucide="alert-circle" class="w-3 h-3 mt-0.5 shrink-0"></i>
                                    <span class="font-medium"><?= esc($item->verification_note) ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($item->files)): ?>
                                    <?php foreach ($item->files as $file): ?>
                                        <?php
                                        $ext = pathinfo($file->original_name, PATHINFO_EXTENSION);
                                        $thumbCls = in_array(strtolower($ext), ['pdf']) ? 'bg-rose-50 border-rose-100 text-rose-500' : 'bg-blue-50 border-blue-100 text-blue-500';
                                        ?>
                                        <button type="button"
                                            onclick="previewFile(<?= htmlspecialchars(json_encode($file)) ?>, this)"
                                            class="file-thumb w-10 h-10 rounded-md border flex flex-col items-center justify-center transition-all hover:shadow-md <?= $thumbCls ?>">
                                            <span class="text-[8px] font-black uppercase"><?= esc($ext) ?></span>
                                            <i data-lucide="eye" class="w-2.5 h-2.5 mt-0.5 opacity-40"></i>
                                        </button>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-[10px] text-slate-300 italic">Belum ada berkas.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Preview Pane -->
    <div class="lg:col-span-4 flex flex-col overflow-hidden">
        <div class="card flex flex-col h-full overflow-hidden bg-slate-900 border-none relative">
            <div id="preview-header" class="absolute top-0 left-0 right-0 z-30 px-3 py-2 bg-slate-900/90 border-b border-white/10 flex items-center justify-between opacity-0 pointer-events-none transition-all">
                <span id="preview-filename" class="text-[10px] text-white/70 truncate uppercase font-bold tracking-widest max-w-[150px]"></span>
                <div class="flex gap-2">
                    <a id="preview-download" href="#" class="p-1.5 text-white/50 hover:text-white"><i data-lucide="download" class="w-4 h-4"></i></a>
                    <button onclick="expandPreview()" class="p-1.5 text-white/50 hover:text-white"><i data-lucide="maximize" class="w-4 h-4"></i></button>
                </div>
            </div>

            <div id="preview-placeholder" class="flex-1 flex flex-col items-center justify-center text-center p-8">
                <i data-lucide="eye-off" class="w-10 h-10 text-slate-700 mb-3"></i>
                <p class="text-xs text-slate-500 font-bold uppercase">Pilih Berkas</p>
                <p class="text-[10px] text-slate-600 mt-1">Klik thumbnail berkas untuk melihat detail.</p>
            </div>

            <div id="preview-pane" class="flex-1 hidden relative flex-col items-center justify-center overflow-hidden">
                <iframe id="pdf-viewer" class="w-full h-full border-none hidden" src=""></iframe>
                <img id="image-viewer" class="max-w-full max-h-full object-contain hidden" src="" alt="">
                <div id="no-preview" class="hidden text-white flex-col items-center gap-3">
                    <i data-lucide="file-warning" class="w-8 h-8 text-amber-500"></i>
                    <p class="text-xs">Preview tidak tersedia.</p>
                    <a id="download-fallback" href="#" class="btn-primary text-xs py-2 px-4">Unduh Berkas</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="fullscreen-overlay" class="fixed inset-0 z-100 bg-black/95 hidden">
    <button onclick="closeFullscreen()" class="absolute top-5 right-5 text-white/50 hover:text-white"><i data-lucide="x" class="w-8 h-8"></i></button>
    <div id="fullscreen-content" class="w-full h-full flex items-center justify-center p-10"></div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }

    .file-thumb.active {
        border-color: #6366f1;
        background-color: #4f46e5;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
</style>

<script>
    let currentPreview = null;

    function previewFile(file, btn) {
        currentPreview = file;
        document.querySelectorAll('.file-thumb').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');

        document.getElementById('preview-placeholder').classList.add('hidden');
        document.getElementById('preview-pane').classList.remove('hidden');
        document.getElementById('preview-pane').classList.add('flex');
        document.getElementById('preview-header').classList.remove('opacity-0', 'pointer-events-none');

        document.getElementById('preview-filename').innerText = file.original_name;
        document.getElementById('preview-download').href = '<?= base_url('travel/student/download') ?>/' + file.id;
        document.getElementById('download-fallback').href = '<?= base_url('travel/student/download') ?>/' + file.id;

        const url = '<?= base_url('travel/student/file') ?>/' + file.id;
        const isPdf = file.file_path.toLowerCase().endsWith('.pdf');
        const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(file.file_path);

        const pdfViewer = document.getElementById('pdf-viewer');
        const imgViewer = document.getElementById('image-viewer');
        const noPreview = document.getElementById('no-preview');

        pdfViewer.classList.add('hidden');
        imgViewer.classList.add('hidden');
        noPreview.classList.add('hidden');

        if (isPdf) {
            pdfViewer.src = url;
            pdfViewer.classList.remove('hidden');
        } else if (isImg) {
            imgViewer.src = url;
            imgViewer.classList.remove('hidden');
        } else {
            noPreview.classList.remove('hidden');
        }
    }

    function expandPreview() {
        if (!currentPreview) return;
        const url = '<?= base_url('travel/student/file') ?>/' + currentPreview.id;
        const isPdf = currentPreview.file_path.toLowerCase().endsWith('.pdf');
        document.getElementById('fullscreen-overlay').classList.remove('hidden');
        document.getElementById('fullscreen-content').innerHTML = isPdf ?
            `<iframe src="${url}" class="w-full h-full border-none"></iframe>` :
            `<img src="${url}" class="max-w-full max-h-full object-contain shadow-2xl">`;
    }

    function closeFullscreen() {
        document.getElementById('fullscreen-overlay').classList.add('hidden');
        document.getElementById('fullscreen-content').innerHTML = '';
    }

    async function verifyAll() {
        const {
            isConfirmed
        } = await Swal.fire({
            title: 'Approve Dokumentasi?',
            text: 'Seluruh berkas yang diunggah mahasiswa akan disetujui.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Ya, Approve'
        });
        if (isConfirmed) submit('verify');
    }

    async function rejectAll() {
        const {
            value: note,
            isConfirmed
        } = await Swal.fire({
            title: 'Reject Dokumentasi?',
            input: 'textarea',
            inputPlaceholder: 'Tulis alasan penolakan...',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Reject',
            inputValidator: (v) => !v ? 'Alasan penolakan wajib diisi!' : undefined
        });
        if (isConfirmed) submit('reject', note);
    }

    async function submit(action, note = '') {
        try {
            const res = await axios.post(`<?= base_url('travel/student/' . $request->id . '/verify') ?>/${action}`, {
                verification_note: note
            }, {
                headers: {
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            });
            if (res.data.status === 'success') {
                Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    .then(() => window.location.reload());
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: e.response?.data?.message || 'Error occurred'
            });
        }
    }
</script>
<?= $this->endSection() ?>