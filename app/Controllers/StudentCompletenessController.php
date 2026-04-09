<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TravelRequestModel;
use App\Models\StudentTravelMemberModel;
use App\Models\StudentTravelExpenseItemModel;
use App\Models\StudentTravelCompletenessModel;
use App\Models\SignatoriesModel;

class StudentCompletenessController extends BaseController
{
    protected $travelRequestModel;
    protected $studentMemberModel;
    protected $expenseItemModel;
    protected $completenessModel;
    protected $signatoriesModel;

    public function __construct()
    {
        $this->travelRequestModel = new TravelRequestModel();
        $this->studentMemberModel = new StudentTravelMemberModel();
        $this->expenseItemModel = new StudentTravelExpenseItemModel();
        $this->completenessModel = new StudentTravelCompletenessModel();
        $this->signatoriesModel  = new SignatoriesModel();
    }

    /**
     * Show the enrichment form for Student Travel (Keuangan only)
     */
    public function enrichment(int $id)
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak. Hanya bagian Keuangan yang dapat melengkapi data.');
        }

        $request = $this->travelRequestModel->find($id);
        if (!$request || $request->category !== 'mahasiswa') {
            return redirect()->to('/travel')->with('error', 'Data perjalanan mahasiswa tidak ditemukan.');
        }

        // Only allow editing in draft or active status
        if ($request->status !== 'draft' && $request->status !== 'active') {
             return redirect()->to("/travel/{$id}")->with('error', 'Data perjalanan tidak dapat diubah pada status ini.');
        }

        $members = $this->studentMemberModel->getByRequestId($id);
        
        $expenseItems = [];
        foreach ($members as $member) {
            $expenseItems[$member->id] = $this->expenseItemModel->where('travel_member_id', $member->id)->findAll();
        }
        
        $existingChecklist = $this->completenessModel->getByRequestId($id);
        $signatories = $this->signatoriesModel->getAllWithEmployee();

        // Group signatories by role
        $groupedSignatories = [
            'PPK'       => [],
            'Bendahara' => [],
        ];

        foreach ($signatories as $sig) {
            $roleKey = null;
            if (str_contains($sig->jabatan, '(PPK)')) {
                $roleKey = 'PPK';
            } elseif (str_contains($sig->jabatan, 'Bendahara') && !str_contains($sig->jabatan, 'Pembantu')) {
                $roleKey = 'Bendahara';
            }

            if ($roleKey) {
                $groupedSignatories[$roleKey][] = $sig;
            }
        }

        return view('travel/student/completeness', [
            'request'            => $request,
            'members'            => $members,
            'expenseItems'       => $expenseItems,
            'existingChecklist'  => $existingChecklist,
            'groupedSignatories' => $groupedSignatories,
            'title'              => 'Lengkapi Data Perjalanan Mahasiswa',
        ]);
    }

    /**
     * Store enrichment data for Student Travel
     */
    public function storeEnrichment(int $id)
    {
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request = $this->travelRequestModel->find($id);
        if (!$request || $request->category !== 'mahasiswa') {
            return redirect()->to('/travel')->with('error', 'Permintaan tidak valid.');
        }

        $validation = $this->validate([
            'ppk_id'       => 'required',
            'bendahara_id' => 'required',
            'expense_items' => 'required',
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Update Travel Request
            $this->travelRequestModel->update($id, [
                'ppk_id'       => $this->request->getPost('ppk_id'),
                'bendahara_id' => $this->request->getPost('bendahara_id'),
                'mak'          => $this->request->getPost('mak'),
                'status'       => 'active',
            ]);

            // 2. Process Expenses
            $expenseData = $this->request->getPost('expense_items');
            $totalBudget = 0;

            foreach ($expenseData as $memberId => $items) {
                // Clear existing items for this member
                $this->expenseItemModel->where('travel_member_id', $memberId)->delete();

                foreach ($items as $item) {
                    $amount = (float) str_replace(['.', ','], ['', '.'], $item['amount'] ?? 0);
                    if ($amount <= 0) continue;

                    // Fallback for item_name if empty (using category label)
                    $itemName = $item['item_name'];
                    if (empty($itemName)) {
                        $labels = [
                            'pocket_money'  => 'Uang Saku',
                            'transport'     => 'Transport Lokal',
                            'ticket'        => 'Tiket',
                            'accommodation' => 'Akomodasi',
                            'other'         => 'Biaya Lainnya'
                        ];
                        $itemName = $labels[$item['category']] ?? 'Kebutuhan Perjalanan';
                    }

                    $totalBudget += $amount;

                    $this->expenseItemModel->insert([
                        'travel_member_id' => $memberId,
                        'category'         => $item['category'],
                        'item_name'        => $itemName,
                        'amount'           => $amount,
                    ]);
                }
            }

            // Update total budget
            $this->travelRequestModel->update($id, ['total_budget' => $totalBudget]);

            // 3. Process Checklist (Dedicated Student Table)
            $checklistItems = $this->request->getPost('checklist') ?? [];
            $existingDbItems = $this->completenessModel->where('travel_request_id', $id)->findAll();
            $existingItemNames = array_unique(array_column($existingDbItems, 'item_name'));

            // Remove items no longer in form
            foreach ($existingItemNames as $oldName) {
                if (!in_array($oldName, $checklistItems)) {
                    // Safety check: only delete if no files associated (we'll check file table)
                    $itemIds = array_column(
                        $this->completenessModel
                            ->where('travel_request_id', $id)
                            ->where('item_name', $oldName)
                            ->findAll(),
                        'id'
                    );

                    if (!empty($itemIds)) {
                        $fileModel = new \App\Models\StudentTravelCompletenessFileModel();
                        $hasFiles = $fileModel->whereIn('completeness_id', $itemIds)->countAllResults() > 0;
                        
                        if (!$hasFiles) {
                            $this->completenessModel->whereIn('id', $itemIds)->delete();
                        }
                    }
                }
            }

            // Add new items for the Leader (Representative) ONLY
            $leader = $this->studentMemberModel->where('travel_request_id', $id)
                ->where('is_representative', 1)
                ->first();

            if ($leader) {
                foreach ($checklistItems as $newName) {
                    if (!empty($newName) && !in_array($newName, $existingItemNames)) {
                        $this->completenessModel->insert([
                            'travel_request_id' => $id,
                            'student_member_id' => $leader->id,
                            'item_name'         => $newName,
                            'status'            => 'pending',
                        ]);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan data pengayaan mahasiswa.');
            }

            return redirect()->to("/travel/student/{$id}")->with('success', 'Data perjalanan mahasiswa berhasil dilengkapi.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
