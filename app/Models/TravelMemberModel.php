<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelMemberModel extends Model
{
    protected $table            = 'travel_members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'travel_request_id',
        'employee_id',
        'kode_golongan',
        'nama_golongan',
        'no_sppd',
        'tgl_sppd',
        'keterangan',
        'report_narrative',
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
        'employee_id'       => 'required|integer|is_not_unique[employees.id]',
        'no_sppd'           => 'permit_empty|string|max_length[100]',
        'tgl_sppd'          => 'permit_empty|valid_date',
        'keterangan'        => 'permit_empty|string|max_length[255]',
    ];

    protected $validationMessages = [
        'travel_request_id' => [
            'required'      => 'Travel request harus dipilih.',
            'is_not_unique' => 'Travel request tidak ditemukan.',
        ],
        'employee_id' => [
            'required'      => 'Pegawai harus dipilih.',
            'is_not_unique' => 'Pegawai tidak ditemukan.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get members with employee details for a travel request
     */
    public function getByRequestWithEmployee(int $travelRequestId): array
    {
        return $this->select('travel_members.*, employees.name as employee_name, employees.nik as employee_nik, employees.nip as employee_nip, employees.user_id, employees.pangkat_golongan as employee_golongan, travel_members.kode_golongan, travel_members.nama_golongan')
            ->join('employees', 'employees.id = travel_members.employee_id')
            ->where('travel_members.travel_request_id', $travelRequestId)
            ->findAll();
    }

    /**
     * Get members by travel request ID
     */
    public function getByRequest(int $travelRequestId): array
    {
        return $this->where('travel_request_id', $travelRequestId)->findAll();
    }
}
