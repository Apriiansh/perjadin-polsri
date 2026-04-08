<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TravelRequestModel;
use App\Models\TravelMemberModel;
use App\Models\TravelExpenseModel;
use App\Models\TravelExpenseItemModel;
use App\Models\TravelCompletenessModel;
use App\Models\SignatoriesModel;
use App\Models\EmployeeModel;

class CompletenessController extends BaseController
{
    protected $travelRequestModel;
    protected $travelMemberModel;
    protected $travelExpenseModel;
    protected $travelExpenseItemModel;
    protected $travelCompletenessModel;
    protected $signatoriesModel;
    protected $employeeModel;

    public function __construct()
    {
        $this->travelRequestModel = new TravelRequestModel();
        $this->travelMemberModel = new TravelMemberModel();
        $this->travelExpenseModel = new TravelExpenseModel();
        $this->travelExpenseItemModel = new TravelExpenseItemModel();
        $this->travelCompletenessModel = new TravelCompletenessModel();
        $this->signatoriesModel = new SignatoriesModel();
        $this->employeeModel = new EmployeeModel();
    }

    /**
     * Show the enrichment form (Keuangan only)
     */
    public function enrichment(int $id)
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya bagian Keuangan yang dapat melengkapi data.');
        }

        $request = $this->travelRequestModel->find($id);
        if (!$request) {
            return redirect()->to('/travel/requests')->with('error', 'Data perjalanan tidak ditemukan.');
        }

        $isSuperadmin = auth()->user()->inGroup('superadmin');
        if (!$isSuperadmin && $request->status !== 'draft' && $request->status !== 'active') {
            return redirect()->to("/travel/{$id}")->with('error', 'Data perjalanan tidak dapat diubah pada status ini.');
        }

        $members = $this->travelMemberModel->getByRequestWithEmployee($id);
        
        // Fetch existing data for Edit mode
        $expenseItems = [];
        $expenses = [];
        foreach ($members as $member) {
            $expenseItems[$member->id] = $this->travelExpenseItemModel->getByMember($member->id);
            $expenses[$member->id] = $this->travelExpenseModel->where('travel_member_id', $member->id)->first();
        }
        
        $existingChecklist = $this->travelCompletenessModel->getByRequestId($id);
        $signatories = $this->signatoriesModel->getAllWithEmployee();

        // Group signatories by role for easier selection
        $groupedSignatories = [
            'PPK'       => [],
            'Bendahara' => [],
        ];

        foreach ($signatories as $sig) {
            $roleKey = match (true) {
                str_contains($sig->jabatan, '(PPK)') => 'PPK',
                str_contains($sig->jabatan, 'Bendahara') && !str_contains($sig->jabatan, 'Pembantu') => 'Bendahara',
                default => null
            };

            if ($roleKey && isset($groupedSignatories[$roleKey])) {
                $groupedSignatories[$roleKey][] = $sig;
            }
        }

        return view('travel/completeness', [
            'request' => $request,
            'members' => $members,
            'expenseItems' => $expenseItems,
            'expenses' => $expenses,
            'existingChecklist' => $existingChecklist,
            'groupedSignatories' => $groupedSignatories,
            'title' => 'Lengkapi Data Perjalanan',
        ]);
    }

    /**
     * Store enrichment data and activate travel request
     */
    public function storeEnrichment(int $id)
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request = $this->travelRequestModel->find($id);
        $isSuperadmin = auth()->user()->inGroup('superadmin');
        if (!$request || (!$isSuperadmin && $request->status !== 'draft' && $request->status !== 'active')) {
            return redirect()->to('/travel')->with('error', 'Permintaan tidak valid atau data sudah terkunci.');
        }

        $validation = $this->validate([
            'ppk_id' => 'required',
            'bendahara_id' => 'required',
            'members.*.kode_golongan' => 'required',
            'members.*.nama_golongan' => 'required',
            'expense_items' => 'required', // JSON or handle array
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Update Travel Request Signatories & MAK
            $this->travelRequestModel->update($id, [
                'ppk_id' => $this->request->getPost('ppk_id'),
                'bendahara_id' => $this->request->getPost('bendahara_id'),
                'mak' => $this->request->getPost('mak'),
                'status' => 'active', // Activate it!
            ]);

            // 2. Update Member Snapshot (Golongan)
            $memberData = $this->request->getPost('members');
            foreach ($memberData as $memberId => $data) {
                $this->travelMemberModel->update($memberId, [
                    'kode_golongan' => $data['kode_golongan'],
                    'nama_golongan' => $data['nama_golongan'],
                ]);
            }

            // 3. Process Itemized Expenses & Standard Costs
            $existingMembers = $this->travelMemberModel->where('travel_request_id', $id)->findAll();
            $expenseItems = $this->request->getPost('expense_items');
            $stdCosts = $this->request->getPost('std_costs') ?? []; // New manual costs
            $totalRequestBudget = 0;

            foreach ($existingMembers as $member) {
                // Get manual standard costs or default to 0
                $uangHarian = (float) str_replace(['.', ','], ['', '.'], $stdCosts[$member->id]['uang_harian'] ?? '0');
                $uangRepresentasi = (float) str_replace(['.', ','], ['', '.'], $stdCosts[$member->id]['uang_representasi'] ?? '0');

                $items = $expenseItems[$member->id] ?? [];
                $categoryTotals = [
                    'tiket' => 0,
                    'penginapan' => 0,
                    'transport_darat' => 0,
                    'transport_lokal' => 0,
                    'lain-lain' => 0,
                ];

                $this->travelExpenseItemModel->where('travel_member_id', $member->id)->delete();

                foreach ($items as $item) {
                    if (empty($item['item_name']) || empty($item['amount'])) continue;

                    $amount = (float) str_replace(['.', ','], ['', '.'], $item['amount']);
                    $categoryTotals[$item['category']] += $amount;

                    $this->travelExpenseItemModel->insert([
                        'travel_member_id' => $member->id,
                        'category' => $item['category'],
                        'item_name' => $item['item_name'],
                        'amount' => $amount,
                    ]);
                }

                // Update the main TravelExpense record for this member
                $expense = $this->travelExpenseModel->where('travel_member_id', $member->id)->first();
                if ($expense) {
                    $newTotal = $uangHarian + $uangRepresentasi + array_sum($categoryTotals);
                    $this->travelExpenseModel->update($expense->id, [
                        'uang_harian' => $uangHarian,
                        'uang_representasi' => $uangRepresentasi,
                        'tiket' => $categoryTotals['tiket'],
                        'penginapan' => $categoryTotals['penginapan'],
                        'transport_darat' => $categoryTotals['transport_darat'],
                        'transport_lokal' => $categoryTotals['transport_lokal'] + $categoryTotals['lain-lain'],
                        'total_biaya' => $newTotal,
                    ]);
                    $totalRequestBudget += $newTotal;
                }
            }

            // Update total budget in travel request
            $this->travelRequestModel->update($id, [
                'total_budget' => $totalRequestBudget,
                'status' => 'active'
            ]);

            // 4. Create Checklist Items from the form (Per Member - Phase 28)
            $this->travelCompletenessModel->where('travel_request_id', $id)->delete();
            $checklistItems = $this->request->getPost('checklist');
            if ($checklistItems) {
                foreach ($existingMembers as $member) {
                    foreach ($checklistItems as $itemName) {
                        if (empty($itemName)) continue;
                        $this->travelCompletenessModel->insert([
                            'travel_request_id' => $id,
                            'member_id' => $member->id,
                            'item_name' => $itemName,
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan data pengayaan.');
            }

            return redirect()->to("/travel/{$id}")->with('success', 'Data perjalanan berhasil dilengkapi dan diaktifkan.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
