<?php

namespace App\Models;

use CodeIgniter\Model;

class TariffModel extends Model
{
    protected $table            = 'tariffs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'province',
        'city',
        'tingkat_biaya',
        'uang_harian',
        'uang_representasi',
        'penginapan',
        'jenis_penginapan',
        'tahun_berlaku',
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
        'province' => 'required|max_length[100]',
        'city' => 'permit_empty|max_length[100]',
        'tingkat_biaya' => 'required|in_list[A,B,C,D]',
        'uang_harian' => 'required|numeric',
        'uang_representasi' => 'required|numeric',
        'penginapan' => 'required|numeric',
        'jenis_penginapan' => 'permit_empty|max_length[100]',
        'tahun_berlaku' => 'required|exact_length[4]|numeric',
        'is_active' => 'required|in_list[0,1]',
    ];
    protected $validationMessages   = [
        'province' => [
            'required' => 'Nama provinsi wajib diisi',
            'max_length[100]' => 'Maksimal 100 karakter'
        ],
        'tingkat_biaya' => [
            'required' => 'Tingkat biaya wajib diisi',
            'in_list[A,B,C,D]' => 'Tingkat nilai harus bernilai A, B, C, atau D'
        ],
        'uang_harian' => [
            'required' => 'Uang harian wajib diisi',
            'numeric' => 'Harus bertipe number'
        ],
        'uang_representasi' => [
            'required' => 'Uang representasi wajib diisi',
            'exact_length[4]' => 'Masukkan format tahun',
            'numeric' => 'Harus bertipe number'
        ],
        'penginapan' => [
            'required' => 'Penginapan wajib diisi',
            'numeric' => 'Harus bertipe number'
        ],
        'tahun_berlaku' => [
            'required' => 'Tahun berlaku wajib diisi',
            'numeric' => 'Harus bertipe number'
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
}
