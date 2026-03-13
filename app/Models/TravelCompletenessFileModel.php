<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelCompletenessFileModel extends Model
{
    protected $table            = 'travel_completeness_files';
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

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No updated_at needed for this table

    // Validation
    protected $validationRules = [
        'completeness_id' => 'required|integer|is_not_unique[travel_completeness.id]',
        'file_path'       => 'required|string|max_length[255]',
        'original_name'   => 'required|string|max_length[255]',
        'file_size'       => 'required|integer',
        'uploaded_by'     => 'required|integer',
    ];

    /**
     * Get all files for a completeness item
     */
    public function getByCompletenessId(int $completenessId): array
    {
        return $this->where('completeness_id', $completenessId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
