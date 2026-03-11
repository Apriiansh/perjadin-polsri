<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelRequestModel extends Model
{
    protected $table            = 'travel_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'no_surat_tugas',
        'tgl_surat_tugas',
        'nomor_surat_rujukan',
        'tgl_surat_rujukan',
        'instansi_pengirim_rujukan',
        'perihal_surat_rujukan',
        'transportation_type',
        'destination_province',
        'destination_city',
        'lokasi',
        'departure_place',
        'departure_date',
        'return_date',
        'duration_days',
        'total_budget',
        'budget_burden_by',
        'tahun_anggaran',
        'tgl_mulai',
        'tgl_selesai',
        'lampiran_path',
        'lampiran_original_name',
        'status',
        'created_by',
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
        'no_surat_tugas'              => 'required|string|max_length[100]',
        'tgl_surat_tugas'             => 'required|valid_date',
        'nomor_surat_rujukan'         => 'required|string|max_length[100]',
        'tgl_surat_rujukan'           => 'required|valid_date',
        'instansi_pengirim_rujukan'   => 'required|string|max_length[200]',
        'perihal_surat_rujukan'       => 'required|string',
        'mak'                         => 'permit_empty|string|max_length[100]',
        'transportation_type'         => 'permit_empty|string',
        'destination_province'        => 'required|string|max_length[100]',
        'destination_city'            => 'required|string|max_length[100]',
        'lokasi'                      => 'permit_empty|string|max_length[255]',
        'departure_place'             => 'permit_empty|string|max_length[255]',
        'departure_date'              => 'permit_empty|valid_date',
        'return_date'                 => 'permit_empty|valid_date',
        'duration_days'               => 'permit_empty|integer',
        'total_budget'                => 'permit_empty|numeric',
        'budget_burden_by'            => 'required|string|max_length[100]',
        'tahun_anggaran'              => 'required|integer',
        'tgl_mulai'                   => 'required|valid_date',
        'tgl_selesai'                 => 'required|valid_date',
        'lampiran_path'               => 'permit_empty|string|max_length[255]',
        'lampiran_original_name'      => 'permit_empty|string|max_length[255]',
        'status'                      => 'permit_empty|in_list[draft,active,completed,cancelled]',
        'created_by'                  => 'permit_empty|integer',
    ];

    protected $validationMessages   = [
        'no_surat_tugas' => [
            'required' => 'Nomor surat tugas harus diisi.',
        ],
        'tgl_surat_tugas' => [
            'required'   => 'Tanggal surat tugas harus diisi.',
            'valid_date' => 'Format tanggal surat tugas tidak valid.',
        ],
        'destination_province' => [
            'required' => 'Provinsi tujuan harus dipilih.',
        ],
        'destination_city' => [
            'required' => 'Kota tujuan harus diisi.',
        ],
        'budget_burden_by' => [
            'required' => 'Pembebanan anggaran harus diisi.',
        ],
        'nomor_surat_rujukan' => [
            'required' => 'Nomor surat rujukan harus diisi.',
        ],
        'tgl_surat_rujukan' => [
            'required'   => 'Tanggal surat rujukan harus diisi.',
            'valid_date' => 'Format tanggal surat rujukan tidak valid.',
        ],
        'instansi_pengirim_rujukan' => [
            'required' => 'Instansi pengirim harus diisi.',
        ],
        'perihal_surat_rujukan' => [
            'required' => 'Perihal surat rujukan harus diisi.',
        ],
        'tahun_anggaran' => [
            'required' => 'Tahun anggaran harus diisi.',
        ],
        'tgl_mulai' => [
            'required'   => 'Tanggal mulai kegiatan harus diisi.',
            'valid_date' => 'Format tanggal mulai tidak valid.',
        ],
        'tgl_selesai' => [
            'required'   => 'Tanggal selesai kegiatan harus diisi.',
            'valid_date' => 'Format tanggal selesai tidak valid.',
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

    public function getMyRequests(int $userId): array
    {
        return $this->where('created_by', $userId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function getDetail(int $id)
    {
        return $this->select('travel_requests.*, users.username as creator_username')
            ->join('users', 'users.id = travel_requests.created_by', 'left')
            ->where('travel_requests.id', $id)
            ->first();
    }

    public function getAllRequests(): array
    {
        return $this->select('travel_requests.*, users.username as creator_username')
            ->join('users', 'users.id = travel_requests.created_by', 'left')
            ->orderBy('travel_requests.created_at', 'DESC')
            ->findAll();
    }
}
