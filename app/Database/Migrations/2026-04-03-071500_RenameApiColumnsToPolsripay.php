<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameApiColumnsToPolsripay extends Migration
{
    public function up()
    {
        // Rename api_employee_id to polsripay_id
        $this->forge->modifyColumn('employees', [
            'api_employee_id' => [
                'name' => 'polsripay_id',
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'api_created_at' => [
                'name' => 'polsripay_created_at',
                'type' => 'DATETIME',
                'null' => true,
            ],
            'api_updated_at' => [
                'name' => 'polsripay_updated_at',
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        // Update index if exists (some DBs auto-update, but let's be safe)
        // Check if index exists before dropping or adding
        $this->db->query("DROP INDEX IF EXISTS idx_api_id ON employees");
        $this->db->query("CREATE INDEX idx_polsripay_id ON employees (polsripay_id)");
    }

    public function down()
    {
        $this->forge->modifyColumn('employees', [
            'polsripay_id' => [
                'name' => 'api_employee_id',
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'polsripay_created_at' => [
                'name' => 'api_created_at',
                'type' => 'DATETIME',
                'null' => true,
            ],
            'polsripay_updated_at' => [
                'name' => 'api_updated_at',
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->db->query("DROP INDEX IF EXISTS idx_polsripay_id ON employees");
        $this->db->query("CREATE INDEX idx_api_id ON employees (api_employee_id)");
    }
}
