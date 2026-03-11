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
                $('.dt-search input').addClass('form-input rounded-lg border-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500');
                $('.dt-length select').addClass('form-select rounded-lg border-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500');
            }
        });
    }
});
