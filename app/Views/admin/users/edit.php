<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-lg mx-auto">
    <!-- Back link -->
    <a href="<?= base_url('admin/users') ?>" class="mb-5 inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition-colors">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Kembali ke Manage User
    </a>

    <div class="card">
        <!-- Header -->
        <div class="mb-6 flex items-center gap-3">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-md bg-accent-100">
                <i data-lucide="user-cog" class="h-5 w-5 text-accent-600"></i>
            </span>
            <div>
                <h1 class="text-lg font-extrabold text-slate-900">Edit User</h1>
                <p class="text-sm text-slate-500">Edit data akun <span class="font-semibold text-slate-700"><?= esc($user->username) ?></span></p>
            </div>
        </div>

        <!-- User Info -->
        <div class="mb-5 rounded-md border border-surface-200 bg-surface-50 px-4 py-3 text-sm">
            <div class="flex items-center gap-2 text-slate-500">
                <i data-lucide="user" class="h-4 w-4 shrink-0"></i>
                <span class="font-mono font-semibold text-slate-700"><?= esc($user->username) ?></span>
            </div>
            <div class="mt-2 flex flex-wrap gap-1.5">
                <?php 
                $config = config('AuthGroups');
                foreach ($user->getGroups() as $g): 
                    $title = $config->groups[$g]['title'] ?? $g;
                ?>
                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                        <?= esc($title) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Edit Form -->
        <form action="<?= base_url('admin/users/' . $user->id . '/update') ?>" method="post" class="space-y-5">
            <?= csrf_field() ?>

            <!-- Email -->
            <div>
                <label class="form-label" for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="input-control"
                    value="<?= esc($user->email ?? '') ?>" required>
            </div>

            <!-- Role -->
            <div>
                <label class="form-label" for="group">Hak Akses (Role)</label>
                <select id="group" name="group" class="input-control" required>
                    <?php foreach ($availableGroups as $key => $info): ?>
                        <option value="<?= esc($key) ?>" <?= $user->inGroup($key) ? 'selected' : '' ?>>
                            <?= esc($info['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1.5 text-xs text-slate-400">Role lama akan dihapus dan diganti dengan role yang dipilih.</p>
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="<?= base_url('admin/users') ?>" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary inline-flex items-center gap-2">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>