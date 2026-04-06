<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'polsripay_id',
        'nik',
        'nip',
        'nuptk',
        'name',
        'pangkat_golongan',
        'jabatan',
        'jafun',
        'rekening_bank',
        'id_jurusan',
        'nama_jurusan',
        'status',
        'synced_at',
        'polsripay_created_at',
        'polsripay_updated_at',
    ];
}
