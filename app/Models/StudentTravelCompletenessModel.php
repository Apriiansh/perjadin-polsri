<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentTravelCompletenessModel extends Model
{
    protected $table            = 'travel_student_completeness';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'travel_request_id',
        'student_member_id',
        'item_name',
        'payment_method',
        'remark',
        'status',
        'verified_by',
        'verified_at',
        'verification_note',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'travel_request_id' => 'required|integer',
        'item_name'         => 'required|string|max_length[255]',
        'payment_method'    => 'permit_empty|in_list[reimbursement,vendor,non_reimbursement]',
        'status'            => 'permit_empty|in_list[pending,uploaded,verified,rejected]',
    ];

    public function getByRequestId(int $requestId): array
    {
        return $this->where('travel_request_id', $requestId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
