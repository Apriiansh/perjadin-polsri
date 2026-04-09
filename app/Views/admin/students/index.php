<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-1 text-sm text-slate-500">Kelola data mahasiswa dan akun login mereka.</p>
    </div>
</div>

<div class="card p-0 border-none shadow-soft bg-white overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative max-w-xs w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i data-lucide="search" class="h-4 w-4"></i>
            </span>
            <input type="text" id="studentSearch" class="input-control pl-10 text-sm" placeholder="Cari Nama atau NIM...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left" id="studentsTable">
            <thead class="text-xs font-black uppercase tracking-widest text-slate-400 bg-slate-50/50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4">Mahasiswa</th>
                    <th class="px-6 py-4">Perjadin Terakhir</th>
                    <th class="px-6 py-4">Status Akun</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data mahasiswa.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr class="student-row hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 block"><?= esc($s->name) ?></span>
                                <span class="text-xs font-mono text-slate-400 uppercase tracking-tighter">
                                    <?= esc($s->nim) ?> | <?= esc($s->prodi) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($s->last_travel_id): ?>
                                    <a href="<?= base_url('travel/student/' . $s->last_travel_id) ?>" class="group">
                                        <div class="text-xs">
                                            <p class="font-bold text-primary-600 group-hover:underline">
                                                <?= esc($s->destination_city) ?>, <?= esc($s->destination_province) ?>
                                            </p>
                                            <p class="text-slate-400 flex items-center gap-1 mt-0.5">
                                                <i data-lucide="calendar" class="w-2.5 h-2.5"></i>
                                                <?= date('d/m/Y', strtotime($s->departure_date)) ?>
                                            </p>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-slate-300 italic">Belum ada riwayat</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($s->user_id): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700 border border-emerald-100 uppercase">
                                        <span class="h-1 w-1 rounded-full bg-emerald-600"></span>
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500 border border-slate-200 uppercase">
                                        Belum Ada
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="<?= base_url('admin/students/' . $s->id . '/edit') ?>" class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-md transition-all" title="Edit Data">
                                        <i data-lucide="edit-3" class="h-4 w-4"></i>
                                    </a>
                                    <a href="<?= base_url('admin/students/' . $s->id) ?>" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-md transition-all" title="Detail">
                                        <i data-lucide="eye" class="h-4 w-4"></i>
                                    </a>
                                    <?php if ($s->user_id): ?>
                                        <form action="<?= base_url('admin/students/' . $s->id . '/reset-password') ?>" method="post" onsubmit="return confirm('Reset password mahasiswa ini?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition-all" title="Reset Password">
                                                <i data-lucide="key-round" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="<?= base_url('admin/students/' . $s->id . '/destroy') ?>" method="post" onsubmit="return confirm('Hapus data mahasiswa ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-all" title="Hapus">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
    document.getElementById('studentSearch').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.student-row').forEach(row => {
            const name = row.cells[0].innerText.toLowerCase();
            const nim = row.cells[1].innerText.toLowerCase();
            if (name.includes(query) || nim.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
<?= $this->endSection() ?>
