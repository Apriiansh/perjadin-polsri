/**
 * Users Management — DataTables init
 */
document.addEventListener('DOMContentLoaded', () => {
    /* ── DataTables ────────────────────────────────────────── */
    const userTable = document.getElementById('usersTable');

    if ($.fn.DataTable && userTable) {
        $(userTable).DataTable({
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50, 100],
            order: [[0, 'asc']],               // Sort by Username
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Cari user…',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ user',
                infoEmpty: 'Tidak ada data user',
                infoFiltered: '(disaring dari _MAX_ total)',
                zeroRecords: 'Tidak ditemukan user yang cocok',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹',
                    padding: 0
                },
            },
            columnDefs: [
                { orderable: false, targets: [5] }, // Matikan sort untuk kolom Aksi
            ],
            drawCallback: function () {
                // Tambahkan sytling tambahan jika diperlukan setelah tabel di-render
                $('.dt-search input').addClass('form-input rounded-lg border-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500');
                $('.dt-length select').addClass('form-select rounded-lg border-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500');
            }
        });
    }
});
