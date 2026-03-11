window.initTomSelect = function (selector, options = {}) {
    if (document.querySelector(selector)) {
        return new TomSelect(selector, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            ...options
        });
    }
};

document.addEventListener('DOMContentLoaded', function () {
    /**
     * Inisialisasi TomSelect untuk Dropdown Pegawai
     * Reusable logic - dapat dipanggil di form mana saja yang memiliki <select id="employee_id">
     */
    initTomSelect('#employee_id', { placeholder: "-- Cari atau Pilih Pegawai --" });
});
