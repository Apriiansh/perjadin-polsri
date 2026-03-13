<?php

/**
 * View: Travel Data Enrichment (Lengkapi Data)
 * Accessible by: Keuangan (Superadmin)
 */
?>
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
        <p class="mt-1 text-sm text-slate-500">Lengkapi data anggota, biaya riil, dan penandatangan untuk mengaktifkan Surat Tugas.</p>
    </div>
    <a href="<?= base_url('travel/' . $request->id) ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Batal
    </a>
</div>

<form action="<?= base_url('travel/' . $request->id . '/enrichment') ?>" method="POST" id="enrichmentForm">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Signatories & Checklist -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Generic Checklist Management -->
            <div class="card p-6 border-t-4 border-t-primary-500">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 mb-4">
                    <i data-lucide="check-square" class="w-5 h-5 text-primary-500"></i>
                    Checklist Kelengkapan
                </h3>
                <p class="text-xs text-slate-500 mb-4 italic">Item ini akan muncul di dashboard Dosen untuk dilengkapi sebagai syarat pencairan.</p>

                <div id="checklist-container" class="space-y-2">
                    <?php $defaultChecklist = ['Laporan Perjalanan', 'Dokumentasi Kegiatan', 'Tiket & Boarding Pass', 'Daftar Pengeluaran Riil']; ?>
                    <?php foreach ($defaultChecklist as $item): ?>
                        <div class="flex items-center gap-2 bg-slate-50 p-2 rounded border border-slate-200">
                            <input type="text" name="checklist[]" value="<?= esc($item) ?>" class="bg-transparent border-none text-sm flex-1 focus:ring-0 p-0">
                            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="this.parentElement.remove()">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="mt-4 w-full py-2 border border-dashed border-slate-300 rounded text-slate-500 text-xs font-medium hover:bg-slate-50 transition-colors flex items-center justify-center gap-1" onclick="addChecklistItem()">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Item
                </button>
            </div>

            <!-- Signatories Selection -->
            <div class="card p-6 border-t-4 border-t-accent-500">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 mb-6">
                    <i data-lucide="pen-tool" class="w-5 h-5 text-accent-500"></i>
                    Penandatangan & MAK
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="form-label mb-1.5 block font-bold text-primary-700">Mata Anggaran Kegiatan (MAK)</label>
                        <input type="text" name="mak" value="<?= esc($request->mak) ?>" placeholder="Contoh: 01.02.03.045.001..." class="input-control font-mono text-sm border-2 border-primary-100 focus:border-primary-500">
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <label class="form-label mb-1.5 block">PPK <span class="text-red-500">*</span></label>
                        <select name="ppk_id" class="input-control sig-select" required>
                            <option value="">Pilih PPK...</option>
                            <?php foreach ($groupedSignatories['PPK'] as $sig): ?>
                                <option value="<?= $sig->id ?>" <?= $request->ppk_id == $sig->id ? 'selected' : '' ?>><?= esc($sig->employee_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label mb-1.5 block">KPA <span class="text-red-500">*</span></label>
                        <select name="kpa_id" class="input-control sig-select" required>
                            <option value="">Pilih KPA...</option>
                            <?php foreach ($groupedSignatories['KPA'] as $sig): ?>
                                <option value="<?= $sig->id ?>" <?= $request->kpa_id == $sig->id ? 'selected' : '' ?>><?= esc($sig->employee_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label mb-1.5 block">Bendahara <span class="text-red-500">*</span></label>
                        <select name="bendahara_id" class="input-control sig-select" required>
                            <option value="">Pilih Bendahara...</option>
                            <?php foreach ($groupedSignatories['Bendahara'] as $sig): ?>
                                <option value="<?= $sig->id ?>" <?= $request->bendahara_id == $sig->id ? 'selected' : '' ?>><?= esc($sig->employee_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label mb-1.5 block">Bendahara Pengeluaran Pembantu<span class="text-red-500">*</span></label>
                        <select name="bpp_id" class="input-control sig-select" required>
                            <option value="">Pilih Bendahara Pengeluaran Pembantu...</option>
                            <?php foreach ($groupedSignatories['BPP'] as $sig): ?>
                                <option value="<?= $sig->id ?>" <?= $request->bpp_id == $sig->id ? 'selected' : '' ?>><?= esc($sig->employee_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>



            <div class="card p-6 bg-emerald-50 border border-emerald-200 shadow-sm">
                <button type="submit" class="btn-primary w-full justify-center gap-2 py-3 text-base shadow-lg hover:shadow-emerald-200">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                    Simpan & Aktifkan
                </button>
                <p class="text-[10px] text-emerald-700 text-center mt-3 leading-relaxed">
                    Setelah ini status akan berubah menjadi <strong>AKTIF</strong>, Dosen akan berangkat dan melengkapi data berupa dokumentasi.
                </p>
            </div>
        </div>

        <!-- Right Column: Members & Itemized Expenses -->
        <div class="lg:col-span-2 space-y-6 card p-6 border-t-4 border-t-secondary-500">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2 px-2">
                <i data-lucide="users" class="w-5 h-5 text-secondary-500"></i>
                Data Anggota & Rincian Biaya
            </h3>

            <?php foreach ($members as $member): ?>
                <div class="card member-card border-l-4 border-l-secondary-500 p-6 overflow-hidden">
                    <div class="flex items-start justify-between mb-6 pb-4 border-b border-slate-100">
                        <div>
                            <h4 class="font-bold text-slate-900 text-lg"><?= esc($member->employee_name) ?></h4>
                            <p class="text-xs text-slate-500 font-mono"><?= esc($member->employee_nip) ?></p>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Snapshot Golongan</div>
                            <div class="flex gap-2">
                                <input type="text" name="members[<?= $member->id ?>][kode_golongan]" value="<?= esc($member->kode_golongan ?: $member->employee_golongan) ?>" placeholder="Gol" class="input-control text-[11px] py-1 px-2 w-16 text-center" required>
                                <input type="text" name="members[<?= $member->id ?>][nama_golongan]" value="<?= esc($member->nama_golongan) ?>" placeholder="Pangkat" class="input-control text-[11px] py-1 px-2 flex-1" required>
                            </div>
                        </div>
                    </div>

                    <!-- Itemized Expenses Table -->
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <h5 class="text-xs font-black uppercase tracking-widest text-slate-400">Rincian Biaya Riil / Tiket</h5>
                            <button type="button" class="text-primary-600 hover:text-primary-700 text-xs font-bold flex items-center gap-1" onclick="addExpenseRow(<?= $member->id ?>)">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Tambah Biaya
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm" id="expense-table-<?= $member->id ?>">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2 font-semibold text-left w-32">Kategori</th>
                                        <th class="px-3 py-2 font-semibold text-left">Deskripsi / Uraian</th>
                                        <th class="px-3 py-2 font-semibold text-right w-40">Jumlah (Rp)</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <!-- Row Template (Initial rows if needed, or pre-fill with basics) -->
                                    <tr class="expense-row">
                                        <td class="px-3 py-2">
                                            <select name="expense_items[<?= $member->id ?>][0][category]" class="input-control text-xs py-1 px-2 border-none bg-transparent hover:bg-slate-50">
                                                <option value="tiket">Tiket</option>
                                                <option value="penginapan">Penginapan</option>
                                                <option value="transport_darat">Trans Darat</option>
                                                <option value="transport_lokal">Trans Lokal</option>
                                                <option value="lain-lain">Lain-lain</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="expense_items[<?= $member->id ?>][0][item_name]" placeholder="Misal: Garuda GA-123 Palembang - Jakarta" class="input-control text-xs py-1 px-2 border-none bg-transparent hover:bg-slate-50 w-full">
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="text" name="expense_items[<?= $member->id ?>][0][amount]" value="0" class="input-control text-xs py-1 px-2 border-none bg-transparent hover:bg-slate-50 text-right font-semibold number-input" oninput="formatNumber(this)">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" class="text-slate-300 hover:text-red-500 transition-colors" onclick="this.closest('tr').remove()">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </td>
                                    </tr>
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
        // Initialize TomSelect for signatories
        document.querySelectorAll('.sig-select').forEach(el => {
            new TomSelect(el, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    });

    function addChecklistItem() {
        const container = document.getElementById('checklist-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 bg-slate-50 p-2 rounded border border-slate-200 animate-in fade-in slide-in-from-top-1';
        div.innerHTML = `
            <input type="text" name="checklist[]" placeholder="Nama item checklist..." class="bg-transparent border-none text-sm flex-1 focus:ring-0 p-0">
            <button type="button" class="text-slate-400 hover:text-red-500 transition-colors" onclick="this.parentElement.remove()">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        `;
        container.appendChild(div);
        lucide.createIcons();
    }

    let expenseIndex = 100; // Large enough start index to avoid conflict with initial rows

    function addExpenseRow(memberId) {
        const tbody = document.querySelector(`#expense-table-${memberId} tbody`);
        const idx = expenseIndex++;
        const tr = document.createElement('tr');
        tr.className = 'expense-row animate-in fade-in slide-in-from-left-1';
        tr.innerHTML = `
            <td class="px-3 py-2">
                <select name="expense_items[${memberId}][${idx}][category]" class="input-control text-xs py-1 px-2 border-none bg-transparent hover:bg-slate-50">
                    <option value="tiket">Tiket</option>
                    <option value="penginapan">Penginapan</option>
                    <option value="transport_darat">Trans Darat</option>
                    <option value="transport_lokal">Trans Lokal</option>
                    <option value="lain-lain">Lain-lain</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="text" name="expense_items[${memberId}][${idx}][item_name]" placeholder="Deskripsi..." class="input-control text-xs py-1 px-2 border-none bg-transparent hover:bg-slate-50 w-full">
            </td>
            <td class="px-3 py-2 text-right">
                <input type="text" name="expense_items[${memberId}][${idx}][amount]" value="0" class="input-control text-xs py-1 px-2 border-none bg-transparent hover:bg-slate-50 text-right font-semibold number-input" oninput="formatNumber(this)">
            </td>
            <td class="px-3 py-2 text-center">
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