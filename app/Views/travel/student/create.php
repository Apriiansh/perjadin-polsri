<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-1 text-sm text-slate-500">Buat permintaan perjalanan dinas baru untuk kelompok mahasiswa.</p>
    </div>
    <a href="<?= base_url('travel/student') ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
</div>

<div class="max-w-6xl mx-auto">
    <form action="<?= base_url('travel/student/store') ?>" method="post" class="space-y-8" id="studentTravelForm" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Section 1: Data Dasar -->
        <div class="card p-6 border-none shadow-soft bg-white">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-md bg-primary-50 flex items-center justify-center text-primary-600">
                    <i data-lucide="info" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-800 leading-tight">1. Data Dasar</h3>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5">Informasi Dasar Surat Tugas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Nomor Surat Tugas <span class="text-red-500">*</span></label>
                    <input type="text" name="no_surat_tugas" class="input-control" placeholder="Nomor Surat Tugas" required>
                </div>
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Tanggal Surat Tugas <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_surat_tugas" class="input-control" required>
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="form-label mb-2 block">Tempat Berangkat</label>
                    <input type="text" name="departure_place" value="Palembang" class="input-control" placeholder="Contoh: Palembang">
                </div>
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Provinsi Tujuan <span class="text-red-500">*</span></label>
                    <select name="destination_province" id="province" class="input-control" required>
                        <option value="">Memuat data...</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Kota Tujuan <span class="text-red-500">*</span></label>
                    <select name="destination_city" id="city" class="input-control" required>
                        <option value="">Pilih provinsi...</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Tanggal Berangkat <span class="text-red-500">*</span></label>
                    <input type="date" name="departure_date" id="departure_date" class="input-control" required>
                </div>
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Tanggal Kembali <span class="text-red-500">*</span></label>
                    <input type="date" name="return_date" id="return_date" class="input-control" required>
                    <p class="mt-1 text-xs text-slate-400 font-medium italic" id="duration-info"></p>
                </div>
            </div>
        </div>

        <!-- Section 2: Surat Rujukan & Lampiran -->
        <div class="card p-6 border-none shadow-soft bg-white">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-md bg-amber-50 flex items-center justify-center text-amber-600">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-800 leading-tight">2. Surat Rujukan & Lampiran</h3>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5">Dasar Penugasan & Pembiayaan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Nomor Surat Rujukan <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_surat_rujukan" class="input-control" placeholder="Nomor Surat Undangan/Rujukan" required>
                </div>
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Tanggal Surat Rujukan <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_surat_rujukan" class="input-control" required>
                </div>
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Instansi Pengirim <span class="text-red-500">*</span></label>
                    <input type="text" name="instansi_pengirim_rujukan" class="input-control" placeholder="Penyelenggara Kegiatan" required>
                </div>
                <div class="space-y-2">
                    <label class="form-label mb-2 block">Beban Anggaran <span class="text-red-500">*</span></label>
                    <input type="text" name="budget_burden_by" value="DIPA Polsri <?= date('Y') ?>" class="input-control" required>
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="form-label mb-2 block">Tahun Anggaran <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun_anggaran" value="<?= date('Y') ?>" class="input-control" required>
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="form-label mb-2 block">Perihal <span class="text-red-500">*</span></label>
                    <textarea name="perihal" rows="2" class="input-control" placeholder="Tujuan perjalanan dinas mahasiswa..." required></textarea>
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="form-label mb-2 block">Lokasi / Venue <span class="text-slate-400 font-normal italic">(Opsional)</span></label>
                    <input type="text" name="lokasi" class="input-control" placeholder="Misal: Hotel Grand Mercure, Yogyakarta">
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="form-label mb-2 block">Lampiran Surat Tugas / SK <span class="text-red-500">*</span></label>
                    <input type="file" name="lampiran" class="input-control file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                    <p class="mt-1 text-xs text-slate-400">Format: PDF, DOC, DOCX, JPG, PNG. Maks 5MB.</p>
                </div>
            </div>
        </div>

        <!-- Section 3: Mahasiswa & Biaya -->
        <div class="card p-6 border-none shadow-soft bg-white">
            <div class="flex items-center justify-between gap-3 mb-6 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-md bg-violet-50 flex items-center justify-center text-violet-600">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 leading-tight">3. Mahasiswa & Rincian Biaya</h3>
                        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5">Input detail personal & estimasi dana</p>
                    </div>
                </div>
                <button type="button" id="addStudentBtn" class="btn-accent gap-2 text-xs">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Mahasiswa
                </button>
            </div>

            <div class="overflow-x-auto -mx-6 px-6 pb-2">
                <table id="studentsTable" class="w-full min-w-[1200px]">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left w-64">Nama & NIM Mahasiswa</th>
                            <th class="px-4 py-3 text-left w-64">Prodi & Jurusan</th>
                            <th class="px-4 py-3 text-left w-48">Jabatan dalam Tim</th>
                            <th class="px-4 py-3 text-center w-16"></th>
                        </tr>
                    </thead>
                    <tbody id="studentsList" class="divide-y divide-slate-50">
                        <!-- Rows added via JS -->
                    </tbody>
                </table>
            </div>
            <div id="emptyMsg" class="py-12 border-2 border-dashed border-slate-100 rounded-md flex flex-col items-center gap-2 text-slate-400">
                <i data-lucide="users" class="w-10 h-10 opacity-20"></i>
                <p class="text-sm font-medium italic">Klik tombol "Tambah" untuk memasukkan data mahasiswa</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 pb-12">
            <a href="<?= base_url('travel/student') ?>" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary px-8">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Simpan & Lanjutkan
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/js/wilayah.js') ?>"></script>
<script src="<?= base_url('assets/js/student_data.js') ?>"></script>
<script>
    let studentCount = 0;

    function addStudentRow() {
        studentCount++;
        const index = studentCount - 1;
        const isLeader = studentCount === 1;
        
        // Generate Jurusan options
        let jurusanOptions = '<option value="">Pilih Jurusan</option>';
        Object.keys(POLSRI_DEPARTMENTS).forEach(j => {
            jurusanOptions += `<option value="${j}">${j}</option>`;
        });

        const html = `
            <tr class="student-row group hover:bg-slate-50 transition-all text-sm" data-index="${index}">
                <td class="px-4 py-6 text-center">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 text-[11px] font-bold text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">${studentCount}</span>
                </td>
                <td class="px-4 py-6">
                    <div class="space-y-2">
                        <input type="text" name="students[${index}][name]" class="input-control text-xs" placeholder="Nama Mahasiswa" required>
                        <input type="text" name="students[${index}][nim]" class="input-control text-xs" placeholder="NIM" required>
                    </div>
                </td>
                <td class="px-4 py-6">
                    <div class="space-y-2">
                        <select name="students[${index}][jurusan]" class="input-control text-xs jurusan-select" required>
                            ${jurusanOptions}
                        </select>
                        <select name="students[${index}][prodi]" class="input-control text-xs prodi-select" required>
                            <option value="">Pilih Jurusan Dulu</option>
                        </select>
                    </div>
                </td>
                <td class="px-4 py-6">
                    <select name="students[${index}][jabatan]" class="input-control text-xs" required>
                        <option value="Ketua" ${isLeader ? 'selected' : ''}>Ketua Tim</option>
                        <option value="Anggota" ${!isLeader ? 'selected' : ''}>Anggota</option>
                        <option value="Perwakilan">Perwakilan</option>
                    </select>
                    ${isLeader ? '<p class="mt-1 text-[10px] text-amber-600 font-bold uppercase tracking-tighter">* Akun otomatis dibuat</p>' : ''}
                </td>
                <td class="px-4 py-6 text-center">
                    ${!isLeader ? `
                    <button type="button" class="remove-student text-slate-300 hover:text-rose-500 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                    ` : ''}
                </td>
            </tr>
        `;

        document.getElementById('studentsList').insertAdjacentHTML('beforeend', html);
        document.getElementById('emptyMsg').classList.add('hidden');
        
        // Add listener for this specific row's jurusan select
        const newRow = document.querySelector(`.student-row[data-index="${index}"]`);
        const jSelect = newRow.querySelector('.jurusan-select');
        const pSelect = newRow.querySelector('.prodi-select');
        
        jSelect.addEventListener('change', () => {
            updateProdiOptions(jSelect, pSelect);
        });

        lucide.createIcons();
    }

    document.getElementById('addStudentBtn').addEventListener('click', addStudentRow);

    document.getElementById('studentsList').addEventListener('click', (e) => {
        if (e.target.closest('.remove-student')) {
            e.target.closest('tr').remove();
            if (document.querySelectorAll('.student-row').length === 0) {
                document.getElementById('emptyMsg').classList.remove('hidden');
            }
        }
    });

    // Duration Logic
    document.addEventListener('DOMContentLoaded', () => {
        const tglMulai = document.getElementById('departure_date');
        const tglSelesai = document.getElementById('return_date');
        const durationInfo = document.getElementById('duration-info');

        function calcDuration() {
            if (tglMulai.value && tglSelesai.value) {
                const start = new Date(tglMulai.value);
                const end = new Date(tglSelesai.value);
                const days = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
                durationInfo.textContent = days > 0 ? 'Durasi: ' + days + ' hari' : 'Tanggal selesai harus >= tanggal mulai';
            } else {
                durationInfo.textContent = '';
            }
        }

        tglMulai.addEventListener('change', calcDuration);
        tglSelesai.addEventListener('change', calcDuration);

        // Default: one student
        addStudentRow();
    });
</script>
<?= $this->endSection() ?>