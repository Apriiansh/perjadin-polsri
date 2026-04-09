<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentTravelMemberModel extends Model
{
    protected $table            = 'travel_student_members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'travel_request_id',
        'student_id',
        'jabatan',
        'is_representative',
        'report_narrative',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'is_representative' => 'boolean',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'travel_request_id' => 'required|integer',
        'student_id'        => 'required|integer',
        'jabatan'           => 'required|string|max_length[100]',
        'is_representative' => 'permit_empty|in_list[0,1]',
    ];

    public function getByRequestId(int $requestId)
    {
        return $this->select('travel_student_members.*, students.nim, students.name, students.prodi, students.jurusan')
            ->join('students', 'students.id = travel_student_members.student_id')
            ->where('travel_request_id', $requestId)
            ->findAll();
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
