/**
 * Employees page — DataTables init + Sync button UX
 */
document.addEventListener('DOMContentLoaded', () => {
    /* ── DataTables ────────────────────────────────────────── */
    if ($.fn.DataTable && document.getElementById('employeesTable')) {
        $('#employeesTable').DataTable({
            pageLength: 15,
            lengthMenu: [10, 15, 25, 20, 50, 100],
            order: [[1, 'asc']],               // sort by Nama
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Cari pegawai…',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ pegawai',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(disaring dari _MAX_ total)',
                zeroRecords: 'Tidak ditemukan pegawai yang cocok',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹',
                },
            },
            columnDefs: [
                { width: '12%', targets: 0 }, // NIP
                { width: '22%', targets: 1 }, // Nama
                { width: '18%', targets: 2 }, // Jabatan
                { width: '10%', targets: 3 }, // Jafung
                {
                    width: '8%',
                    targets: 4,
                    render: function (data, type) {
                        if (type === 'display' && data) {
                            return data.replace(/Golongan\s+/i, '');
                        }
                        return data;
                    }
                }, // Golongan
                { width: '12%', targets: 5 }, // Jurusan
                { width: '8%', targets: 6 }, // Status
                { orderable: false, width: '10%', targets: 7 }, // Akun
            ],
        });
    }

    /* ── Sync button spinner ───────────────────────────────── */
    const syncForm = document.getElementById('syncForm');
    const syncBtn = document.getElementById('syncBtn');
    const syncIcon = document.getElementById('syncIcon');
    const syncLabel = document.getElementById('syncLabel');

    if (syncForm && syncBtn) {
        syncForm.addEventListener('submit', () => {
            syncBtn.disabled = true;
            syncBtn.classList.add('opacity-70', 'pointer-events-none');
            if (syncIcon) syncIcon.classList.add('animate-spin');
            if (syncLabel) syncLabel.textContent = 'Menyinkronkan…';
        });
    }
});
