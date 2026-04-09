<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentTravelExpenseItemModel extends Model
{
    protected $table            = 'travel_student_expense_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'travel_member_id',
        'category',
        'item_name',
        'amount',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'amount' => 'float',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'travel_member_id' => 'required|integer',
        'category'         => 'required|in_list[pocket_money,transport,ticket,accommodation,other]',
        'item_name'        => 'required|string|max_length[255]',
        'amount'           => 'required|numeric',
    ];

    public function getByMemberId(int $memberId)
    {
        return $this->where('travel_member_id', $memberId)->findAll();
    }
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
