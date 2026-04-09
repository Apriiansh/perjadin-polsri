/**
 * POLSRI Department & Study Program Mapping
 * Managed as a source of truth for all student-related forms.
 */
const POLSRI_DEPARTMENTS = {
    "Teknik Sipil": [
        "DIII Teknik Sipil", 
        "DIV Perancangan Jalan dan Jembatan",
        "DIV Perancangan Jalan dan Jembatan PSDKU OKU",
        "DIV Arsitektur Bangunan Gedung"
    ],
    "Teknik Mesin": [
        "DIII Teknik Mesin", 
        "DIII Pemeliharaan Alat Berat",
        "DIV Teknik Mesin Produksi dan Perawatan",
        "DIV Teknik Mesin Produksi dan Perawatan PSDKU Kab. Siak Prov. Riau"
    ],
    "Teknik Elektro": [
        "DIII Teknik Listrik", 
        "DIII Teknik Elektronika", 
        "DIII Teknik Telekomunikasi",
        "DIV Teknik Elektro", 
        "DIV Teknik Telekomunikasi", 
        "DIV Teknologi Rekayasa Instalasi Listrik"
    ],
    "Teknik Kimia": [
        "DIII Teknik Kimia", 
        "DIII Teknik Kimia PSDKU Kab. Siak Prov. Riau",
        "DIV Teknologi Kimia Industri", 
        "DIV Teknik Energi", 
        "S2 Terapan Teknik Energi Terbarukan"
    ],
    "Akuntansi": [
        "DIII Akuntansi", 
        "DIV Akuntansi Sektor Publik",
        "DIV Akuntansi Sektor Publik PSDKU OKU Baturaja",
        "DIV Akuntansi Sektor Publik Kab. Siak Prov. Riau"
    ],
    "Administrasi Bisnis": [
        "DIII Administrasi Bisnis", 
        "DIII Administrasi Bisnis PSDKU OKU Baturaja",
        "DIV Manajemen Bisnis", 
        "DIV Bisnis Digital", 
        "DIV Usaha Perjalanan Wisata", 
        "S2 Terapan Pemasaran, Inovasi, dan Teknologi"
    ],
    "Teknik Komputer": [
        "DIII Teknik Komputer", 
        "DIV Teknologi Informatika Multimedia Digital"
    ],
    "Manajemen Informatika": [
        "DIII Manajemen Informatika", 
        "DIV Manajemen Informatika"
    ],
    "Bahasa dan Pariwisata": [
        "DIII Bahasa Inggris", 
        "DIV Bahasa Inggris untuk Komunikasi Bisnis dan Profesional"
    ],
    "Rekayasa Teknologi dan Bisnis Pertanian": [
        "DIII Teknologi Pangan Kampus Banyuasin", 
        "DIV Teknologi Produksi Tanaman Perkebunan",
        "DIV Agribisnis Pangan Kampus Banyuasin", 
        "DIV Manajemen Agribisnis Kampus Banyuasin",
        "DIV Teknologi Akuakultur", 
        "DIV Teknologi Rekayasa Pangan"
    ]
};

/**
 * Helper to populate a study program select based on department
 * @param {HTMLSelectElement} jurusanSelect 
 * @param {HTMLSelectElement} prodiSelect 
 * @param {string} currentProdi - Optional initial value
 */
function updateProdiOptions(jurusanSelect, prodiSelect, currentProdi = '') {
    const selectedJurusan = jurusanSelect.value;
    const prodis = POLSRI_DEPARTMENTS[selectedJurusan] || [];
    
    // Clear existing
    prodiSelect.innerHTML = '<option value="">Pilih Program Studi</option>';
    
    // Add new
    prodis.forEach(prodi => {
        const option = document.createElement('option');
        option.value = prodi;
        option.textContent = prodi;
        if (prodi === currentProdi) {
            option.selected = true;
        }
        prodiSelect.appendChild(option);
    });
}
