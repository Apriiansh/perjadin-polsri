$(document).ready(function () {
    // Initialize DataTables
    const table = $('#tariffsTable').DataTable({
        "pageLength": 10,
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data tarif",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ tarif",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 tarif",
            "infoFiltered": "(disaring dari _MAX_ total tarif)",
            "paginate": {
                "first": "<<",
                "last": ">>",
                "next": ">",
                "previous": "<"
            },
            "emptyTable": "Belum ada data tarif"
        },
        "order": [[0, "asc"]], // Sort by Provinsi by default
        "columnDefs": [
            { "orderable": false, "targets": -1 } // Disable sorting on action column
        ],
        // Tailwind CSS integration for DataTables structure
        "dom": '<"flex flex-col sm:flex-row justify-between items-center p-4 border-b border-surface-200 gap-4"<"flex items-center gap-2"l><"flex items-center gap-2"f>>rt<"flex flex-col sm:flex-row justify-between items-center p-4 border-t border-surface-200 gap-4"ip>'
    });

    // Setup CSRF token for AJAX requests if needed in the future
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="X-CSRF-TOKEN"]').attr('content')
        }
    });
});
