// Golongan PNS mapping: kode → nama
const GOLONGAN_MAP = {
    'IV/e': 'Pembina Utama',
    'IV/d': 'Pembina Utama Madya',
    'IV/c': 'Pembina Utama Muda',
    'IV/b': 'Pembina TK. I',
    'IV/a': 'Pembina',
    'III/d': 'Penata TK. I',
    'III/c': 'Penata',
    'III/b': 'Penata Muda TK. 1',
    'III/a': 'Penata Muda',
    'II/d': 'Pengatur TK. I',
    'II/c': 'Pengatur',
    'II/b': 'Pengatur Muda TK. 1',
    'II/a': 'Pengatur Muda',
    'I/d': 'Juru TK. I',
    'I/c': 'Juru',
    'I/b': 'Juru Muda TK. 1',
    'I/a': 'Juru Muda',
};

/**
 * Filter golongan options by angka prefix (IV, III, II, I).
 * If angka is empty, returns all options.
 */
function getGolonganOptions(angka) {
    if (!angka || angka === '-') return Object.keys(GOLONGAN_MAP);
    // Extract Roman numeral from formats like "Golongan III", "Golongan IV", or plain "III", "IV"
    const match = angka.toUpperCase().trim().match(/\b(IV|III|II|I)\b/);
    if (!match) return Object.keys(GOLONGAN_MAP);
    const prefix = match[1];
    return Object.keys(GOLONGAN_MAP).filter(code => code.startsWith(prefix + '/'));
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', function () {
    let membersSelect;
    const originalSelect = document.getElementById('members');

    if (originalSelect) {
        // Function to extract member data safely
        function extractMemberData(val) {
            // First check if it's in our stored custom object
            if (membersSelect.options[val]) {
                return membersSelect.options[val];
            }
            return null;
        }

        // Initialize TomSelect with AJAX loading
        membersSelect = new TomSelect('#members', {
            create: false,
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'nip'],
            sortField: {
                field: "name",
                direction: "asc"
            },
            placeholder: "Ketik nama atau NIP pegawai...",
            hideSelected: true, 
            preload: 'focus', // Load when user clicks
            load: function(query, callback) {
                const golVal = document.getElementById('filter-golongan')?.value || '';
                const jurVal = document.getElementById('filter-jurusan')?.value || '';
                
                const url = new URL(window.location.origin + '/travel/employees');
                if (query) url.searchParams.append('q', query);
                if (golVal) url.searchParams.append('golongan', golVal);
                if (jurVal) url.searchParams.append('jurusan', jurVal);

                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        // Map the complex backend object to flat fields for TomSelect, handling nulls
                        const results = json.map(emp => ({
                            id: emp.id,
                            name: emp.name,
                            nip: emp.nip || '',
                            golongan: emp.pangkat_golongan || '-',
                            jurusan: emp.nama_jurusan || '-'
                        }));
                        callback(results);
                    }).catch(()=>{
                        callback();
                    });
            },
            render: {
                // Dropdown option
                option: function(item, escape) {
                    return `
                        <div>
                            <span class="block font-bold">` + escape(item.name) + `</span>
                            <span class="block text-xs text-slate-500">` + escape(item.nip) + ` • ` + escape(item.golongan) + ` • ` + escape(item.jurusan) + `</span>
                        </div>
                    `;
                },
                // Return an empty div for selected items inside the input box
                item: function(data, escape) {
                    return '<div style="display:none;" data-id="' + escape(data.id) + '"></div>';
                }
            },
            onChange: function () {
                renderSelectedMembers();
                debounceCheckTariffs();
            }
        });

        // Sync initial options from HTML (for Edit mode) into TomSelect's internal options format
        Array.from(originalSelect.options).forEach(opt => {
            if (opt.selected) {
                 membersSelect.addOption({
                    id: opt.value,
                    name: opt.getAttribute('data-name') || opt.text,
                    nip: opt.getAttribute('data-nip') || '',
                    golongan: opt.getAttribute('data-golongan') || '-',
                    jurusan: opt.getAttribute('data-jurusan') || '-'
                });
            }
        });
        
        // Listen for filter changes to refresh dropdown options via AJAX
        const filterGolongan = document.getElementById('filter-golongan');
        const filterJurusan = document.getElementById('filter-jurusan');

        function triggerFilter() {
            membersSelect.clearOptions(); // Clear old cached options
            membersSelect.clearCache(); // Clear Sifter Cache
            membersSelect.load(""); // Trigger a reload with empty query to fetch filtered list
            membersSelect.open(); // Open dropdown to show user results
        }

        if (filterGolongan) filterGolongan.addEventListener('change', triggerFilter);
        if (filterJurusan) filterJurusan.addEventListener('change', triggerFilter);

        // Render selected items securely outside the input field
        function renderSelectedMembers() {
            const listContainer = document.getElementById('selected-members-list');
            const countSpan = document.getElementById('selected-count');

            if (!listContainer) return;

            const emptyMsg = document.getElementById('empty-members-msg');
            listContainer.innerHTML = '';

            const selectedValues = membersSelect.getValue(); // returns an array
            if (countSpan) countSpan.textContent = selectedValues.length;

            if (selectedValues.length === 0) {
                if (emptyMsg) {
                    emptyMsg.style.display = 'block';
                    listContainer.appendChild(emptyMsg);
                }
                return;
            }

            if (emptyMsg) {
                emptyMsg.style.display = 'none';
                listContainer.appendChild(emptyMsg); 
            }

            selectedValues.forEach(val => {
                const emp = extractMemberData(val);
                if (!emp) return;

                // Get existing golongan data (edit mode)
                const existing = (window.existingMemberGolongan && window.existingMemberGolongan[emp.id]) || {};
                const empAngka = emp.golongan || '';
                const options = getGolonganOptions(empAngka);

                let selectHtml = '<option value="">Pilih...</option>';
                options.forEach(code => {
                    const selected = existing.kode_golongan === code ? ' selected' : '';
                    selectHtml += '<option value="' + escapeHtml(code) + '"' + selected + '>' + escapeHtml(code) + ' — ' + escapeHtml(GOLONGAN_MAP[code]) + '</option>';
                });
                const namaGolValue = existing.nama_golongan || '';

                const card = document.createElement('div');
                card.className = 'p-3 border border-slate-200 rounded-md bg-white shadow-sm';
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="font-bold text-sm text-slate-800">${escapeHtml(emp.name)}</span>
                            <span class="text-xs text-slate-500 font-mono">${escapeHtml(emp.nip)} • Gol API: ${escapeHtml(emp.golongan)} • Unit: ${escapeHtml(emp.jurusan)}</span>
                        </div>
                        <button type="button" class="text-red-500 p-1.5 hover:bg-red-50 rounded-md transition-colors" data-remove-id="${emp.id}" title="Hapus Pegawai">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <div class="mt-2 pt-2 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Kode Golongan <span class="text-red-400">*</span></label>
                            <select name="member_golongan[${emp.id}]" class="input-control text-xs py-1.5 golongan-select" data-emp-id="${emp.id}">
                                ${selectHtml}
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Nama Golongan</label>
                            <input type="text" name="member_nama_golongan[${emp.id}]" class="input-control text-xs py-1.5 bg-slate-50 nama-golongan-input" data-emp-id="${emp.id}" value="${escapeHtml(namaGolValue)}" readonly>
                        </div>
                    </div>
                `;
                listContainer.appendChild(card);
            });

            // Re-bind remove events
            listContainer.querySelectorAll('[data-remove-id]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idToRemove = this.getAttribute('data-remove-id');
                    membersSelect.removeItem(idToRemove);
                });
            });

            // Bind golongan select change → auto-fill nama golongan
            listContainer.querySelectorAll('.golongan-select').forEach(sel => {
                sel.addEventListener('change', function() {
                    const empId = this.getAttribute('data-emp-id');
                    const code = this.value;
                    const namaInput = listContainer.querySelector('.nama-golongan-input[data-emp-id="' + empId + '"');
                    if (namaInput) {
                        namaInput.value = GOLONGAN_MAP[code] || '';
                    }
                });
                // Trigger change to set initial nama_golongan if pre-selected
                if (sel.value) {
                    sel.dispatchEvent(new Event('change'));
                }
            });
        }

        // Run render initially (for edit page mainly)
        renderSelectedMembers();
    }

    // -----------------------------------------------------
    // Elemen terkait AJAX Tarif (Tidak berubah banyak)
    // -----------------------------------------------------
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const warningContainer = document.getElementById('tariff-warning-container');
    const warningList = document.getElementById('tariff-warning-list');

    // Listener ke perubahan input destinasi
    if (provinceSelect) provinceSelect.addEventListener('change', debounceCheckTariffs);
    if (citySelect) citySelect.addEventListener('change', debounceCheckTariffs);

    let debounceTimer;

    function debounceCheckTariffs() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(checkTariffs, 500); // 500ms delay
    }

    async function checkTariffs() {
        if (!provinceSelect || !membersSelect) return;
        const province = provinceSelect.options[provinceSelect.selectedIndex]?.text;
        const city = citySelect && citySelect.selectedIndex > 0 ? citySelect.options[citySelect.selectedIndex].text : '';
        const selectedMembers = membersSelect.getValue(); // ini nge-return array values

        // Reset UI
        if (warningContainer) warningContainer.style.display = 'none';
        if (warningList) warningList.innerHTML = '';

        if (!province || selectedMembers.length === 0 || province.includes('Sedang memuat') || province === '') {
            return;
        }

        const formData = new FormData();
        formData.append('province', province);
        formData.append('city', city);
        formData.append('members', JSON.stringify(selectedMembers));

        try {
            const response = await fetch('/travel/check-tariff', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            if (result.status === 'success' && result.missing_tariffs.length > 0 && warningList && warningContainer) {
                result.missing_tariffs.forEach(item => {
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${item.name}</strong> (Tingkat ${item.tingkat_biaya}): Data tidak ditemukan.`;
                    warningList.appendChild(li);
                });

                warningContainer.style.display = 'flex';
                warningContainer.classList.remove('hidden');
            }

        } catch (error) {
            console.error('Error checking tariffs:', error);
        }
    }
});
