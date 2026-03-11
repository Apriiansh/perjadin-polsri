<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterEmployeesAddApiFields extends Migration
{
    public function up()
    {
        $fields = [
            'nik' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'after' => 'api_employee_id',
            ],
            'nuptk' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'after' => 'nip',
            ],
            'jafun' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'after' => 'jabatan',
            ],
            'id_jurusan' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'rekening_bank',
            ],
            'nama_jurusan' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'after' => 'id_jurusan',
            ],
            'api_created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'synced_at',
            ],
            'api_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'api_created_at',
            ],
        ];

        $this->forge->addColumn('employees', $fields);

        $this->db->query('CREATE UNIQUE INDEX employees_nik_unique ON employees (nik)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX employees_nik_unique ON employees');

        $this->forge->dropColumn('employees', [
            'nik',
            'nuptk',
            'jafun',
            'id_jurusan',
            'nama_jurusan',
            'api_created_at',
            'api_updated_at',
        ]);
    }
}
