<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($title) ?>
<?= $this->endSection() ?>

<?= $this->section('headStyles') ?>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.default.css" rel="stylesheet">
<link href="<?= base_url('assets/css/tom-select-custom.css') ?>" rel="stylesheet">
<style>
    .member-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .member-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-full mx-auto mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900"><?= esc($title) ?></h1>
        <p class="mt-1 text-sm text-slate-500">Lengkapi tarif biaya, checklist dokumen, dan penandatangan untuk tim mahasiswa.</p>
    </div>
    <a href="<?= base_url('travel/student/' . $request->id) ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Batal
    </a>
</div>

<form action="<?= base_url('travel/student/' . $request->id . '/enrichment') ?>" method="POST" id="enrichmentForm">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Signatories & Checklist -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Checklist Management -->
            <div class="card p-6 border-t-4 border-t-blue-600 rounded-md">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 mb-4">
                    <i data-lucide="check-square" class="w-5 h-5 text-blue-600"></i>
                    Checklist Kelengkapan
                </h3>
                <p class="text-xs text-slate-500 mb-4 italic">Item ini akan muncul di dashboard Mahasiswa (Ketua Tim) untuk dilengkapi.</p>

                <div id="checklist-container" class="space-y-2">
                    <?php 
                    $itemsToDisplay = !empty($existingChecklist) 
                        ? array_unique(array_map(function($item) {
                            return is_object($item) ? $item->item_name : $item['item_name'];
                        }, $existingChecklist)) 
                        : ['Laporan Kegiatan', 'Dokumentasi / Foto', 'Daftar Hadir', 'Nota Transportasi', 'Bukti Pengeluaran Riil']; 
                    ?>
                    <?php foreach ($itemsToDisplay as $item): ?>
                        <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-md border border-slate-200">
                            <input type="text" name="checklist[]" value="<?= esc($item) ?>" class="bg-transparent border-none text-sm flex-1 focus:ring-0 p-0 font-medium text-slate-700">
                            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="this.parentElement.remove()">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="mt-4 w-full py-2 border border-dashed border-slate-300 rounded-md text-slate-500 text-xs font-medium hover:bg-slate-50 transition-colors flex items-center justify-center gap-1" onclick="addChecklistItem()">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Item
                </button>
            </div>

            <!-- Signatories Selection -->
            <div class="card p-6 border-t-4 border-t-slate-600 rounded-md">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 mb-6">
                    <i data-lucide="pen-tool" class="w-5 h-5 text-slate-600"></i>
                    Penandatangan & MAK
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="form-label mb-1.5 block font-bold text-slate-700">Mata Anggaran (MAK)</label>
                        <input type="text" name="mak" value="<?= esc($request->mak) ?>" placeholder="Contoh: 01.02.03..." class="input-control font-mono text-sm border-2 border-slate-100 focus:border-blue-500 rounded-md">
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <label class="form-label mb-1.5 block text-xs">PPK <span class="text-red-500">*</span></label>
                        <select name="ppk_id" class="input-control sig-select" required>
                            <option value="">Pilih PPK...</option>
                            <?php foreach ($groupedSignatories['PPK'] as $sig): ?>
                                <option value="<?= $sig->id ?>" <?= $request->ppk_id == $sig->id ? 'selected' : '' ?>><?= esc($sig->employee_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label mb-1.5 block text-xs">Bendahara Pengeluaran <span class="text-red-500">*</span></label>
                        <select name="bendahara_id" class="input-control sig-select" required>
                            <option value="">Pilih Bendahara...</option>
                            <?php foreach ($groupedSignatories['Bendahara'] as $sig): ?>
                                <option value="<?= $sig->id ?>" <?= $request->bendahara_id == $sig->id ? 'selected' : '' ?>><?= esc($sig->employee_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card p-6 bg-emerald-50 border border-emerald-200 shadow-sm rounded-md">
                <button type="submit" class="btn-primary w-full justify-center gap-2 py-3 text-base shadow-lg hover:shadow-emerald-200">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                    Simpan & Aktifkan
                </button>
                <p class="text-[10px] text-emerald-700 text-center mt-3 leading-relaxed">
                    Status akan berubah menjadi <strong>AKTIF</strong>. Ketua Tim mahasiswa dapat login untuk mengunggah dokumentasi.
                </p>
            </div>
        </div>

        <!-- Right Column: Members & Itemized Expenses -->
        <div class="lg:col-span-2 space-y-6 card p-6 border-t-4 border-t-secondary-500 rounded-md">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 px-2">
                <i data-lucide="users" class="w-5 h-5 text-secondary-500"></i>
                Rincian Biaya Anggota Tim
            </h3>

            <?php foreach ($members as $member): ?>
                <div class="card member-card border border-slate-200 rounded-md overflow-hidden mb-6">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-slate-900"><?= esc($member->name) ?></h4>
                            <p class="text-xs text-slate-500 font-mono"><?= esc($member->nim) ?> | <?= esc($member->jabatan) ?></p>
                        </div>
                        <button type="button" class="text-primary-600 hover:text-primary-700 text-xs font-bold flex items-center gap-1" onclick="addExpenseRow(<?= $member->id ?>)">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Tambah Biaya
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm" id="expense-table-<?= $member->id ?>">
                                <thead class="text-slate-400 border-b border-slate-100">
                                    <tr>
                                        <th class="px-3 py-2 font-bold text-[10px] uppercase tracking-wider text-left w-32">Kategori</th>
                                        <th class="px-3 py-2 font-bold text-[10px] uppercase tracking-wider text-left">Deskripsi / Uraian</th>
                                        <th class="px-3 py-2 font-bold text-[10px] uppercase tracking-wider text-right w-40">Jumlah (Rp)</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php 
                                    $items = $expenseItems[$member->id] ?? [];
                                    if (empty($items)):
                                    ?>
                                        <tr class="expense-row">
                                            <td class="px-3 py-3">
                                                <select name="expense_items[<?= $member->id ?>][0][category]" class="input-control text-xs bg-transparent border-none py-1 focus:ring-0">
                                                    <option value="pocket_money">Uang Saku</option>
                                                    <option value="transport">Transport</option>
                                                    <option value="ticket">Tiket</option>
                                                    <option value="accommodation">Akomodasi</option>
                                                    <option value="other">Lain-lain</option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-3">
                                                <input type="text" name="expense_items[<?= $member->id ?>][0][item_name]" placeholder="Uraian biaya (misal: Uang Saku, Transport Lokal, dll)..." class="input-control text-xs border-none bg-transparent w-full focus:ring-0">
                                            </td>
                                            <td class="px-3 py-3 text-right">
                                                <input type="text" name="expense_items[<?= $member->id ?>][0][amount]" value="0" class="input-control text-xs border-none bg-transparent text-right font-bold text-slate-800 number-input focus:ring-0" oninput="formatNumber(this)">
                                            </td>
                                            <td class="px-3 py-3 text-center">
                                                <button type="button" class="text-slate-300 hover:text-red-500 transition-colors" onclick="this.closest('tr').remove()">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($items as $idx => $item): ?>
                                            <tr class="expense-row">
                                                <td class="px-3 py-3">
                                                    <select name="expense_items[<?= $member->id ?>][<?= $idx ?>][category]" class="input-control text-xs bg-transparent border-none py-1 focus:ring-0">
                                                        <option value="pocket_money" <?= $item->category === 'pocket_money' ? 'selected' : '' ?>>Uang Saku</option>
                                                        <option value="transport" <?= $item->category === 'transport' ? 'selected' : '' ?>>Transport</option>
                                                        <option value="ticket" <?= $item->category === 'ticket' ? 'selected' : '' ?>>Tiket</option>
                                                        <option value="accommodation" <?= $item->category === 'accommodation' ? 'selected' : '' ?>>Akomodasi</option>
                                                        <option value="other" <?= $item->category === 'other' ? 'selected' : '' ?>>Lain-lain</option>
                                                    </select>
                                                </td>
                                                    <td class="px-3 py-3">
                                                        <input type="text" name="expense_items[<?= $member->id ?>][<?= $idx ?>][item_name]" value="<?= esc($item->item_name) ?>" placeholder="Uraian biaya..." class="input-control text-xs border-none bg-transparent w-full focus:ring-0">
                                                    </td>
                                                <td class="px-3 py-3 text-right">
                                                    <input type="text" name="expense_items[<?= $member->id ?>][<?= $idx ?>][amount]" value="<?= number_format($item->amount, 0, ',', '.') ?>" class="input-control text-xs border-none bg-transparent text-right font-bold text-slate-800 number-input focus:ring-0" oninput="formatNumber(this)">
                                                </td>
                                                <td class="px-3 py-3 text-center">
                                                    <button type="button" class="text-slate-300 hover:text-red-500 transition-colors" onclick="this.closest('tr').remove()">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.sig-select').forEach(el => {
            new TomSelect(el, { create: false, sortField: { field: "text", direction: "asc" } });
        });
    });

    function addChecklistItem() {
        const container = document.getElementById('checklist-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 bg-slate-50 p-2 rounded-md border border-slate-200 animate-in fade-in slide-in-from-top-1';
        div.innerHTML = `
            <input type="text" name="checklist[]" placeholder="Item baru..." class="bg-transparent border-none text-sm flex-1 focus:ring-0 p-0 font-medium text-slate-700">
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="this.parentElement.remove()">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(div);
        lucide.createIcons();
    }

    let expenseIndex = 1000;
    function addExpenseRow(memberId) {
        const tbody = document.querySelector(`#expense-table-${memberId} tbody`);
        const idx = expenseIndex++;
        const tr = document.createElement('tr');
        tr.className = 'expense-row animate-in fade-in slide-in-from-left-1';
        tr.innerHTML = `
            <td class="px-3 py-3 text-left">
                <select name="expense_items[${memberId}][${idx}][category]" class="input-control text-xs bg-transparent border-none py-1 focus:ring-0">
                    <option value="pocket_money">Uang Saku</option>
                    <option value="transport">Transport</option>
                    <option value="ticket">Tiket</option>
                    <option value="accommodation">Akomodasi</option>
                    <option value="other">Lain-lain</option>
                </select>
            </td>
            <td class="px-3 py-3">
                <input type="text" name="expense_items[${memberId}][${idx}][item_name]" placeholder="Uraian biaya (misal: Uang Saku, Transport Lokal, dll)..." class="input-control text-xs border-none bg-transparent w-full focus:ring-0">
            </td>
            <td class="px-3 py-3 text-right">
                <input type="text" name="expense_items[${memberId}][${idx}][amount]" value="0" class="input-control text-xs border-none bg-transparent text-right font-bold text-slate-800 number-input focus:ring-0" oninput="formatNumber(this)">
            </td>
            <td class="px-3 py-3 text-center">
                <button type="button" class="text-slate-300 hover:text-red-500 transition-colors" onclick="this.closest('tr').remove()">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        lucide.createIcons();
    }

    function formatNumber(el) {
        let val = el.value.replace(/[^0-9]/g, '');
        if (val === '') val = '0';
        el.value = new Intl.NumberFormat('id-ID').format(val);
    }
</script>
<?= $this->endSection() ?>
