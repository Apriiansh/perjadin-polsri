<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentTravelCompletenessFileModel extends Model
{
    protected $table            = 'travel_student_completeness_files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'completeness_id',
        'file_path',
        'original_name',
        'file_size',
        'uploaded_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No updated_at column on this table

    public function getByCompletenessId(int $completenessId): array
    {
        return $this->where('completeness_id', $completenessId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
