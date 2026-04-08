<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Header Section -->
<div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Laporan Perjalanan Dinas</h1>
        <p class="text-sm font-medium text-slate-500">Unduh bundle SPJ (Surat Pertanggungjawaban) lengkap dalam format ZIP.</p>
    </div>
</div>

<!-- Info Alert (Modern Style) -->
<div class="mb-6 flex items-start gap-4 rounded-xl border border-blue-100 bg-blue-50/50 p-4 text-blue-800 shadow-sm">
    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
        <i data-lucide="info" class="h-5 w-5"></i>
    </div>
    <div class="flex-1 space-y-1 pt-1">
        <p class="text-sm font-bold leading-none">Informasi Bundle SPJ</p>
        <p class="text-xs font-medium leading-relaxed opacity-80">
            Laporan SPJ diunduh dalam format <strong>ZIP</strong> yang berisi file PDF (seluruh berkas) serta folder file dokumentasi lampiran asli dari setiap anggota.
        </p>
    </div>
</div>

<!-- Table Card -->
<div class="card overflow-visible p-0 border-none shadow-premium bg-white">
    <div class="overflow-x-auto">
        <table id="reportsTable" class="w-full text-sm">
            <thead class="bg-slate-50/80 text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">No</th>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">Perihal & No. ST</th>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">Pengaju</th>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">Pelaksanaan</th>
                    <th class="px-5 py-5 text-left font-bold uppercase tracking-wider text-[10px]">Kota Tujuan</th>
                    <th class="px-5 py-5 text-center font-bold uppercase tracking-wider text-[10px]">Status</th>
                    <th class="px-5 py-5 text-center font-bold uppercase tracking-wider text-[10px] w-48">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                <?php foreach ($travels as $idx => $travel): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-5 py-4 text-slate-400 font-bold"><?= $idx + 1 ?></td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="font-extrabold text-slate-800 tracking-tight leading-tight line-clamp-1" title="<?= esc($travel->perihal) ?>">
                                    <?= esc($travel->perihal) ?>
                                </span>
                                <div class="flex items-center gap-1.5 leading-none">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">ST: <?= esc($travel->no_surat_tugas ?: '-') ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 shrink-0 bg-slate-100 text-slate-600 rounded-lg items-center justify-center text-[10px] font-bold uppercase border border-slate-200">
                                    <?= substr((string)esc($travel->creator_name ?? 'U'), 0, 1) ?>
                                </div>
                                <div class="flex flex-col min-w-0 leading-tight">
                                    <span class="text-[11px] font-bold text-slate-800 truncate"><?= esc($travel->creator_name) ?></span>
                                    <span class="text-[9px] text-slate-400 font-medium">Pengaju</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-bold text-slate-700 leading-none"><?= date('d M Y', strtotime($travel->departure_date)) ?></span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Berangkat</span>
                                </div>
                                <i data-lucide="move-right" class="w-3 h-3 text-slate-300"></i>
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-bold text-slate-700 leading-none"><?= date('d M Y', strtotime($travel->return_date)) ?></span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Kembali</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1.5 text-slate-700 bg-primary-50 px-2 py-0.5 rounded border border-primary-100 w-fit">
                                <i data-lucide="map-pin" class="w-3 h-3 text-primary-500"></i>
                                <span class="text-[10px] font-bold uppercase tracking-tight"><?= esc($travel->destination_city ?: $travel->destination_province) ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-600 text-[10px] font-extrabold uppercase tracking-widest shadow-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Selesai
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= base_url('travel/' . $travel->id) ?>"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-primary-600 hover:border-primary-200 hover:bg-primary-50 transition-all active:scale-90 shadow-sm"
                                    title="Lihat Detail">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                                <a href="<?= base_url('admin/reports/download/' . $travel->id) ?>"
                                    class="flex h-9 items-center gap-2 px-3 rounded-lg bg-primary-600 text-white text-[10px] font-extrabold uppercase tracking-widest hover:bg-primary-700 shadow-md shadow-primary-500/20 transition-all active:scale-95 group"
                                    title="Download Bundle SPJ (ZIP)">
                                    <i data-lucide="file-archive" class="w-4 h-4 group-hover:animate-bounce"></i>
                                    <span>Download SPJ</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script>
    $(document).ready(function() {
        $('#reportsTable').DataTable({
            "order": [
                [0, "asc"]
            ],
            "pageLength": 10,
            "language": {
                "emptyTable": "Belum ada laporan yang tersedia",
                "search": "_INPUT_",
                "searchPlaceholder": "Cari laporan...",
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_–_END_ dari _TOTAL_ laporan",
                "infoEmpty": "Tidak ada data",
                "infoFiltered": "(disaring dari _MAX_ total)",
                "paginate": {
                    "first": "«",
                    "last": "»",
                    "next": "›",
                    "previous": "‹"
                }
            },
            "drawCallback": function() {
                // Tailwind input styling for DataTables controls (Matching travel.js)
                $('.dt-search input').addClass('form-input h-9 !bg-white/50 !backdrop-blur-sm rounded-lg border-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500 transition-all');
                $('.dt-length select').addClass('form-select h-9 !bg-white/50 !backdrop-blur-sm rounded-lg border-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500 transition-all');

                // Pagination button styling
                $('.dt-paging nav').addClass('flex items-center gap-1');
                $('.dt-paging-button').addClass('flex h-9 min-w-[36px] items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-bold text-slate-600 transition-all hover:bg-slate-50 hover:text-primary-600 active:scale-95');
                $('.dt-paging-button.current').addClass('!bg-primary-600 !border-primary-600 !text-white shadow-md shadow-primary-500/20');
                $('.dt-paging-button.disabled').addClass('opacity-40 pointer-events-none');
            }
        });
    });
</script>
<?= $this->endSection() ?>