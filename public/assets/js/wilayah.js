document.addEventListener('DOMContentLoaded', function () {
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');

    if (provinceSelect) {
        // Ambil nilai lama (old value) dari atribut data-old-value (baik dari validasi error atau DB untuk edit)
        const oldProv = provinceSelect.getAttribute('data-old-value');
        const oldCity = citySelect ? citySelect.getAttribute('data-old-value') : null;

        // Fetch Provinsi
        fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
            .then(response => response.json())
            .then(provinces => {
                provinceSelect.innerHTML = '<option value="">Pilih Provinsi</option>';

                if (provinces && provinces.length > 0) {
                    provinces.forEach(prov => {
                        const option = document.createElement('option');
                        const formattedName = toTitleCase(prov.name);
                        option.value = formattedName;
                        option.textContent = formattedName;
                        option.dataset.kode = prov.id;

                        // Tandai sebagai terpilih jika nilainya cocok dengan oldValue
                        if (oldProv && (oldProv === prov.name || oldProv.toUpperCase() === prov.name.toUpperCase())) {
                            option.selected = true;
                        }

                        provinceSelect.appendChild(option);
                    });

                    // Trigger load city jika province sudah terpilih (untuk mode edit / error validasi)
                    if (oldProv && citySelect) {
                        provinceSelect.dispatchEvent(new Event('change'));
                    }
                } else {
                    provinceSelect.innerHTML = '<option value="">Gagal memuat data provinsi</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching provinces:', error);
                provinceSelect.innerHTML = '<option value="">Gagal memuat API wilayah</option>';
            });

        // Event listener saat provinsi berubah -> Fetch Kota
        if (citySelect) {
            provinceSelect.addEventListener('change', function () {
                const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
                const provId = selectedOption ? selectedOption.dataset.kode : null;

                if (!provId) {
                    citySelect.innerHTML = '<option value="">Pilih provinsi terlebih dahulu</option>';
                    return;
                }

                citySelect.innerHTML = '<option value="">Sedang memuat kota...</option>';

                fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provId}.json`)
                    .then(response => response.json())
                    .then(regencies => {
                        citySelect.innerHTML = '<option value="">Semua Kota/Kabupaten (Opsional)</option>';

                        if (regencies && regencies.length > 0) {
                            regencies.forEach(reg => {
                                const option = document.createElement('option');
                                const formattedName = toTitleCase(reg.name);
                                option.value = formattedName; // Simpan dalam format cantik ke DB
                                option.textContent = formattedName;
                                option.dataset.kode = reg.id;

                                if (oldCity && (oldCity === reg.name || oldCity.toUpperCase() === reg.name.toUpperCase())) {
                                    option.selected = true;
                                }

                                citySelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching regencies:', error);
                        citySelect.innerHTML = '<option value="">Gagal memuat data kota</option>';
                    });
            });
        }
    }

    function toTitleCase(str) {
        if (!str) return '';
        const acronyms = ['DKI', 'DI', 'NTB', 'NTT', 'NAD', 'DIY'];
        return str.toLowerCase().split(' ').map(word => {
            if (acronyms.includes(word.toUpperCase())) {
                return word.toUpperCase();
            }
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }
});
