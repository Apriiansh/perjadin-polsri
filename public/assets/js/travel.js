/**
 * Travel Requests Management — DataTables init
 */
document.addEventListener('DOMContentLoaded', () => {
    /* ── DataTables ────────────────────────────────────────── */
    const travelTable = document.getElementById('travelTable');

    if ($.fn.DataTable && travelTable) {
        $(travelTable).DataTable({
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50, 100],
            order: [[0, 'asc']],               // Sort by No id or No Surat Tugas as default
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Cari pengajuan...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ pengajuan',
                infoEmpty: 'Tidak ada data pengajuan',
                infoFiltered: '(disaring dari _MAX_ total)',
                zeroRecords: 'Tidak ditemukan pengajuan yang cocok',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹',
                    padding: 0
                },
            },
            columnDefs: [
                { orderable: false, targets: [4, 5] }, // Disable sort for Status and Aksi
            ],
            drawCallback: function () {
                // Tailwind input styling for DataTables controls
                $('.dt-search input').addClass('form-input h-9 !bg-white/50 !backdrop-blur-sm rounded-lg border-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500 transition-all');
                $('.dt-length select').addClass('form-select h-9 !bg-white/50 !backdrop-blur-sm rounded-lg border-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500 transition-all');
                
                // Pagination button styling
                $('.dt-paging nav').addClass('flex items-center gap-1');
                $('.dt-paging-button').addClass('flex h-9 min-w-[36px] items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-bold text-slate-600 transition-all hover:bg-slate-50 hover:text-primary-600 active:scale-95');
                $('.dt-paging-button.current').addClass('!bg-primary-600 !border-primary-600 !text-white shadow-md shadow-primary-500/20');
                $('.dt-paging-button.disabled').addClass('opacity-40 pointer-events-none');
            }
        });
    }
});
