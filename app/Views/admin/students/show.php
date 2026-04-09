<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-4">
        <a href="<?= base_url('admin/students') ?>" class="btn-secondary p-2 inline-flex items-center justify-center rounded-md">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($student->name) ?></h1>
            <p class="mt-1 text-sm text-slate-500">NIM: <span class="font-mono"><?= esc($student->nim) ?></span> | <?= esc($student->prodi) ?> - <?= esc($student->jurusan) ?></p>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="<?= base_url('admin/students/' . $student->id . '/edit') ?>" class="btn-secondary gap-2 text-sm shadow-sm bg-white hover:bg-slate-50 hover:text-primary-600">
            <i data-lucide="edit-3" class="h-4 w-4"></i>
            Edit Data
        </a>
        <?php if ($student->user_id): ?>
            <form action="<?= base_url('admin/students/' . $student->id . '/reset-password') ?>" method="post" onsubmit="return confirm('Reset password mahasiswa ini?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn-secondary gap-2 text-sm shadow-sm bg-white hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200">
                    <i data-lucide="key-round" class="h-4 w-4"></i>
                    Reset Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left: Identity Specs -->
    <div class="lg:col-span-1 space-y-6">
        <div class="card p-6 border-none shadow-soft bg-white rounded-md">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i data-lucide="user" class="h-5 w-5 text-primary-500"></i>
                Identitas Mahasiswa
            </h3>
            
            <div class="space-y-4">
                <div class="p-4 rounded-md bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Nama Lengkap</p>
                    <p class="text-sm font-bold text-slate-900"><?= esc($student->name) ?></p>
                </div>
                <div class="p-4 rounded-md bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">NIM</p>
                    <p class="text-sm font-mono font-bold text-slate-700"><?= esc($student->nim) ?></p>
                </div>
                <div class="p-4 rounded-md bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Program Studi / Jurusan</p>
                    <p class="text-sm font-bold text-slate-700"><?= esc($student->prodi) ?></p>
                    <p class="text-xs text-slate-400 uppercase tracking-tighter"><?= esc($student->jurusan) ?></p>
                </div>
                <div class="p-4 rounded-md bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Status Sistem</p>
                    <?php if ($student->user_id): ?>
                        <?php 
                        $users = auth()->getProvider();
                        $user = $users->findById($student->user_id);
                        ?>
                        <div class="mt-1 flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Memiliki Akun Login</span>
                            </div>
                            <?php if ($user): ?>
                                <div class="space-y-1.5 pl-4 border-l-2 border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Username / Email</p>
                                    <p class="text-xs font-mono text-slate-600 break-all"><?= esc($user->username) ?></p>
                                    <p class="text-xs font-mono text-slate-400 break-all"><?= esc($user->email) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="mt-1 text-xs font-bold text-slate-400 uppercase tracking-wider italic">Belum Pernah Dibuatkan Akun</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: History -->
    <div class="lg:col-span-2">
        <div class="card p-6 border-none shadow-soft bg-white rounded-md min-h-[400px]">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i data-lucide="history" class="h-5 w-5 text-indigo-500"></i>
                Riwayat Perjalanan Dinas
            </h3>

            <?php if (empty($travels)): ?>
                <div class="py-20 flex flex-col items-center justify-center text-slate-400 italic">
                    <i data-lucide="inbox" class="h-12 w-12 opacity-10 mb-4"></i>
                    <p>Mahasiswa ini belum pernah mengikuti perjalanan dinas.</p>
                </div>
            <?php else: ?>
                <div class="relative overflow-hidden">
                    <div class="space-y-3">
                        <?php foreach ($travels as $t): ?>
                            <a href="<?= base_url('travel/student/' . $t->travel_request_id) ?>" class="block p-4 rounded-md border border-slate-100 hover:border-primary-200 hover:bg-primary-50/30 transition-all group">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-1">
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-200 text-slate-600 uppercase tracking-tighter">
                                                <?= esc($t->jabatan) ?>
                                            </span>
                                            <p class="text-xs font-mono text-slate-400"><?= esc($t->no_surat_tugas) ?></p>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-800 group-hover:text-primary-700 transition-colors line-clamp-1"><?= esc($t->perihal) ?></h4>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest"><?= date('d M Y', strtotime($t->tgl_surat_tugas)) ?></p>
                                        <div class="mt-1">
                                            <?php 
                                            $statusColors = [
                                                'draft' => 'bg-slate-100 text-slate-600',
                                                'active' => 'bg-emerald-100 text-emerald-700',
                                                'completed' => 'bg-indigo-100 text-indigo-700',
                                                'rejected' => 'bg-rose-100 text-rose-700',
                                            ];
                                            $colorClass = $statusColors[strtolower($t->status)] ?? 'bg-slate-100 text-slate-600';
                                            ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider <?= $colorClass ?>">
                                                <?= esc($t->status) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-primary-400 group-hover:translate-x-1 transition-all"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
