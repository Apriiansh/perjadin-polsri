<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Manage Pegawai</h1>
        <p class="mt-1 text-sm text-slate-500">Data pegawai yang disinkronisasi dari API POLSRI.</p>
    </div>

    <form action="<?= base_url('admin/employees/sync') ?>" method="post" id="syncForm">
        <?= csrf_field() ?>
        <button type="submit" id="syncBtn" class="btn-primary group inline-flex items-center gap-2 text-sm">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            <span id="syncLabel">Sync dari API</span>
        </button>
    </form>
</div>

<!-- Summary badges -->
<div class="mb-5 flex flex-wrap gap-2">
    <span class="inline-flex items-center gap-1.5 rounded-md bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-800">
        <i data-lucide="users" class="w-4 h-4"></i>
        Total: <?= number_format(count($employees ?? [])) ?> pegawai
    </span>
    <?php
    $activeCount = 0;
    foreach ($employees ?? [] as $e) {
        if (($e['status'] ?? '') === '1' || strtolower($e['status'] ?? '') === 'aktif') $activeCount++;
    }
    ?>
    <?php if ($activeCount > 0): ?>
        <span class="inline-flex items-center gap-1.5 rounded-md bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
            <span class="h-1.5 w-1.5 rounded-sm bg-green-500"></span>
            Aktif: <?= number_format($activeCount) ?>
        </span>
    <?php endif; ?>
</div>

<!-- Table card — DataTables injects its own dt-container wrapper inside here -->
<div class="card overflow-hidden p-0">
    <table id="employeesTable" class="w-full text-sm">
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Jafung</th>
                <th>Golongan</th>
                <th>Jurusan</th>
                <th>Status</th>
                <th>Akun</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees ?? [] as $employee) : ?>
                <?php
                $status   = $employee['status'] ?? '-';
                $isActive = $status === '1' || strtolower($status) === 'aktif';
                ?>
                <tr>
                    <td class="font-mono text-xs text-slate-500"><?= esc($employee['nip'] ?? '-') ?></td>
                    <td>
                        <p class="font-semibold capitalize text-slate-900"><?= esc(strtolower($employee['name'] ?? '-')) ?></p>
                        <p class="text-xs text-slate-400"><?= esc($employee['email'] ?? '') ?></p>
                    </td>
                    <td class="text-slate-600"><?= esc($employee['jabatan'] ?? '-') ?></td>
                    <td class="text-slate-600"><?= esc($employee['jafun'] ?? '-') ?></td>
                    <td class="text-slate-600"><?= esc($employee['pangkat_golongan'] ?? '-') ?></td>
                    <td class="text-slate-600"><?= esc($employee['nama_jurusan'] ?? '-') ?></td>
                    <td>
                        <span class="dt-badge <?= $isActive ? 'dt-badge-active' : 'dt-badge-inactive' ?>">
                            <span class="dt-badge-dot"></span>
                            <?= $isActive ? 'Aktif' : esc($status) ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($employee['user_id'])): ?>
                            <span class="inline-flex items-center gap-1 rounded-md bg-secondary-100 px-2 py-1 text-xs font-semibold text-secondary-700">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                Terbuat
                            </span>
                        <?php else: ?>
                            <a href="<?= base_url('admin/users/create/' . esc($employee['id'])) ?>" class="inline-flex items-center gap-1 rounded-md bg-white border border-primary-200 hover:bg-primary-50 px-2 py-1 text-xs font-semibold text-primary-600 transition-colors">
                                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                Buat Akun
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<!-- jQuery + DataTables core only (no framework variant needed, we style ourselves) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="<?= base_url('assets/js/employees.js') ?>" defer></script>
<?= $this->endSection() ?>