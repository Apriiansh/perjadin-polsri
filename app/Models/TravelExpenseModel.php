<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelExpenseModel extends Model
{
    protected $table            = 'travel_expenses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'travel_member_id',
        'tariff_id',
        'uang_harian',
        'uang_representasi',
        'tiket',
        'penginapan',
        'transport_darat',
        'transport_lokal',
        'total_biaya'
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
    protected $validationRules      = [];
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

    public function getByMember(int $travelMemberId)
    {
        return $this->where('travel_member_id', $travelMemberId)->first();
    }

    public function getByRequestWithMember(int $travelRequestId): array
    {
        return $this->select('travel_expenses.*, travel_members.employee_id, travel_members.no_sppd, travel_members.tgl_sppd, travel_members.kode_golongan, travel_members.nama_golongan, employees.name as employee_name, employees.nip as employee_nip, employees.pangkat_golongan as employee_golongan, employees.jabatan as employee_jabatan, employees.rekening_bank, tariffs.tingkat_biaya')
            ->join('travel_members', 'travel_members.id = travel_expenses.travel_member_id')
            ->join('employees', 'employees.id = travel_members.employee_id')
            ->join('tariffs', 'tariffs.id = travel_expenses.tariff_id', 'left')
            ->where('travel_members.travel_request_id', $travelRequestId)
            ->findAll();
    }
}
