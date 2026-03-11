<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Manage Penandatangan</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola master data pejabat penandatangan SPPD & Keuangan.</p>
    </div>

    <a href="<?= base_url('admin/signatories/create') ?>" class="btn-primary inline-flex items-center gap-2 text-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Tambah Penandatangan
    </a>
</div>

<!-- Summary badges -->
<div class="mb-5 flex flex-wrap gap-2">
    <span class="inline-flex items-center gap-1.5 rounded-md bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-800">
        <i data-lucide="users" class="h-4 w-4"></i>
        Total: <?= number_format(count($signatories ?? [])) ?> pejabat
    </span>
</div>

<div class="card overflow-hidden p-0">
    <table id="signatoriesTable" class="w-full text-sm">
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th class="text-center">Status</th>
                <th class="text-center font-bold">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($signatories ?? [] as $sig) : ?>
                <tr>
                    <td class="font-mono text-xs font-bold text-slate-600">
                        <?= esc($sig->nip ?? '-') ?>
                    </td>
                    <td class="font-semibold capitalize text-slate-900"><?= esc(strtolower($sig->employee_name) ?? '-') ?></td>
                    <td>
                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600 shadow-sm">
                            <?= esc($sig->jabatan ?? '-') ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($sig->is_active == 0) : ?>
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
                    <td class="text-center flex justify-center gap-2">
                        <!-- Edit -->
                        <a href="<?= base_url('admin/signatories/' . $sig->id . '/edit') ?>" class="rounded-md border p-1.5 text-slate-500 hover:bg-slate-50" title="Edit Data">
                            <i data-lucide="edit" class="h-4 w-4"></i>
                        </a>
                        <!-- Delete -->
                        <form action="<?= base_url('admin/signatories/' . $sig->id . '/destroy') ?>" method="post" onsubmit="return confirm('Yakin hapus data ini? Tindakan ini tidak dapat dibatalkan.')">
                            <?= csrf_field() ?>
                            <button type="submit" class="rounded-md border p-1.5 text-red-500 hover:bg-red-50" title="Hapus Data">
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
<script>
    $(document).ready(function() {
        $('#signatoriesTable').DataTable({
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari pejabat...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_-_END_ dari _TOTAL_ pejabat",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Tidak ada yang cocok",
                paginate: {
                    first: "«",
                    last: "»",
                    next: "›",
                    previous: "‹"
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>