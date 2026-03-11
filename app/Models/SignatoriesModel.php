<?php

namespace App\Models;

use CodeIgniter\Model;

class SignatoriesModel extends Model
{
    protected $table            = 'signatories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'jabatan',
        'employee_id',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'jabatan'     => 'required|string|max_length[150]',
        'employee_id' => 'required|numeric',
        'is_active'   => 'required|in_list[0,1]',
    ];

    protected $validationMessages   = [
        'jabatan' => [
            'required' => 'Jabatan penandatangan wajib diisi',
        ],
        'employee_id' => [
            'required' => 'Pegawai wajib dipilih',
            'numeric'  => 'Format id pegawai tidak valid'
        ]
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
     * Mengambil semua data penandatangan beserta relasi nama dan NIP pegawainya.
     * Fungsi ini akan mempermudah kamu waktu ngoding SignatoriesController::index().
     */
    public function getAllWithEmployee()
    {
        return $this->select('signatories.*, employees.name as employee_name, employees.nip')
            ->join('employees', 'employees.id = signatories.employee_id', 'left')
            ->orderBy('signatories.created_at', 'DESC')
            ->findAll();
    }
}
