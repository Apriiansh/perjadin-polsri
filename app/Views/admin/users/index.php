<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Manage User</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola data user dan akun login pegawai.</p>
    </div>

    <a href="<?= base_url('admin/employees') ?>" class="btn-primary inline-flex items-center gap-2 text-sm">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        Tambah via Pegawai
    </a>
</div>

<!-- Summary badges -->
<div class="mb-5 flex flex-wrap gap-2">
    <span class="inline-flex items-center gap-1.5 rounded-md bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-800">
        <i data-lucide="user-lock" class="h-4 w-4"></i>
        Total: <?= number_format(count($users ?? [])) ?> user
    </span>
</div>

<div class="card overflow-hidden p-0">
    <table id="usersTable" class="w-full text-sm">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role / Group</th>
                <th class="text-center">Status</th>
                <th>Last Active</th>
                <th class="text-center font-bold">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users ?? [] as $user) : ?>
                <tr>
                    <td class="font-mono text-xs font-bold text-primary-700">
                        <?= esc($user->username ?? '-') ?>
                    </td>
                    <td class="text-slate-600"><?= esc($user->email ?? '-') ?></td>
                    <td>
                        <?php 
                        $config = config('AuthGroups');
                        foreach ($user->getGroups() as $group) : 
                            $title = $config->groups[$group]['title'] ?? $group;
                        ?>
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600 shadow-sm">
                                <?= esc($title) ?>
                            </span>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <?php if ($user->isBanned()) : ?>
                            <span class="dt-badge dt-badge-inactive">
                                <span class="dt-badge-dot bg-red-500"></span>
                                Nonaktif
                            </span>
                        <?php else : ?>
                            <span class="dt-badge dt-badge-active">
                                <span class="dt-badge-dot bg-emerald-500"></span>
                                Aktif
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-xs text-slate-400">
                        <?= $user->last_active ? $user->last_active->humanize() : '-' ?>
                    </td>
                    <td class="text-center flex justify-center gap-2">
                        <!-- Edit Role -->
                        <a href="<?= base_url('admin/users/' . $user->id . '/edit') ?>" class="rounded-md border p-1.5 text-slate-500 hover:bg-slate-50" title="Edit Role">
                            <i data-lucide="user-cog" class="h-4 w-4"></i>
                        </a>
                        <!-- Reset Password -->
                        <form action="<?= base_url('admin/users/' . $user->id . '/reset-password') ?>" method="post" onsubmit="return confirm('Reset password user ini?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="rounded-md border p-1.5 text-amber-600 hover:bg-amber-50" title="Reset Password">
                                <i data-lucide="key-round" class="h-4 w-4"></i>
                            </button>
                        </form>
                        <!-- Toggle Active -->
                        <form action="<?= base_url('admin/users/' . $user->id . '/toggle-active') ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="rounded-md border p-1.5 <?= $user->isBanned() ? 'text-green-600 hover:bg-green-50' : 'text-slate-400 hover:bg-slate-50' ?>" title="<?= $user->isBanned() ? 'Aktifkan' : 'Nonaktifkan' ?>">
                                <i data-lucide="<?= $user->isBanned() ? 'user-check' : 'user-minus' ?>" class="h-4 w-4"></i>
                            </button>
                        </form>
                        <!-- Delete User -->
                        <form action="<?= base_url('admin/users/' . $user->id . '/destroy') ?>" method="post" onsubmit="return confirm('Yakin hapus user <?= esc($user->username) ?>? Tindakan ini tidak dapat dibatalkan.')">
                            <?= csrf_field() ?>
                            <button type="submit" class="rounded-md border p-1.5 text-red-500 hover:bg-red-50" title="Hapus User">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="<?= base_url('assets/js/users.js') ?>" defer></script>
<?= $this->endSection() ?>