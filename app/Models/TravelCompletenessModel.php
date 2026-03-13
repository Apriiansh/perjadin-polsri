<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelCompletenessModel extends Model
{
    protected $table            = 'travel_completeness';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'travel_request_id',
        'item_name',
        'payment_method',
        'remark',
        'document_path',
        'original_name',
        'file_size',
        'uploaded_by',
        'uploaded_at',
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
        'travel_request_id' => 'required|integer|is_not_unique[travel_requests.id]',
        'item_name'         => 'required|string|max_length[255]',
        'payment_method'    => 'permit_empty|in_list[reimbursement,vendor,non_reimbursement]',
        'remark'            => 'permit_empty|string',
        'document_path'     => 'permit_empty|string|max_length[255]',
        'status'            => 'permit_empty|in_list[pending,uploaded,verified,rejected]',
    ];

    protected $validationMessages = [
        'travel_request_id' => [
            'required'      => 'Travel request harus dipilih.',
            'is_not_unique' => 'Travel request tidak ditemukan.',
        ],
        'item_name' => [
            'required' => 'Nama item kelengkapan harus diisi.',
        ],
        'payment_method' => [
            'in_list' => 'Metode pembayaran harus salah satu: reimbursement, vendor, atau non-reimbursement.',
        ],
    ];

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

    /**
     * Get all completeness items for a travel request
     */
    public function getByRequestId(int $requestId): array
    {
        return $this->where('travel_request_id', $requestId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get completeness items filtered by payment method
     */
    public function getByPaymentMethod(string $method): array
    {
        return $this->where('payment_method', $method)
            ->findAll();
    }

    /**
     * Batch insert completeness items for a request
     */
    public function insertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false): int|bool
    {
        return parent::insertBatch($set, $escape, $batchSize, $testing);
    }

    /**
     * Get completeness items with their uploaded files
     */
    public function getByRequestWithFiles(int $requestId): array
    {
        $items = $this->getByRequestId($requestId);
        $fileModel = new \App\Models\TravelCompletenessFileModel();

        foreach ($items as &$item) {
            $item->files = $fileModel->getByCompletenessId($item->id);
        }

        return $items;
    }
}
