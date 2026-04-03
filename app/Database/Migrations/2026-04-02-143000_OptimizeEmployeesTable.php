<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OptimizeEmployeesTable extends Migration
{
    public function up()
    {
        // 1. Drop existing columns that are no longer needed
        if ($this->db->fieldExists('email', 'employees')) {
            $this->forge->dropColumn('employees', 'email');
        }
        if ($this->db->fieldExists('tingkat_biaya', 'employees')) {
            $this->forge->dropColumn('employees', 'tingkat_biaya');
        }

        // 2. Ensure all required columns exist with correct types
        $fields = [
            'api_employee_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'nuptk' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'pangkat_golongan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jafun' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'rekening_bank' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'id_jurusan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nama_jurusan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'aktif',
            ],
            'synced_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'api_created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'api_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        foreach ($fields as $name => $attributes) {
            if ($this->db->fieldExists($name, 'employees')) {
                $this->forge->modifyColumn('employees', [$name => $attributes]);
            } else {
                $this->forge->addColumn('employees', [$name => $attributes]);
            }
        }

        // 3. Add Index for performance (safely)
        $this->db->query("ALTER TABLE employees ADD INDEX IF NOT EXISTS idx_api_id (api_employee_id)");
        $this->db->query("ALTER TABLE employees ADD INDEX IF NOT EXISTS idx_nip (nip)");
    }

    public function down()
    {
        // No down needed as this is a cleanup/reorganization
    }
}
