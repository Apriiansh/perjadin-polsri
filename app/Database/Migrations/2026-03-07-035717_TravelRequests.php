<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TravelRequests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'employee_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'no_surat_tugas' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tgl_surat_tugas' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'no_sppd' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tgl_sppd' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'mak' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'purpose' => [
                'type' => 'TEXT',
            ],
            'transportation_type' => [
                'type'       => 'ENUM',
                'constraint' => ['pesawat', 'darat', 'laut'],
                'null'       => true,
            ],
            'origin' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'destination' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'destination_province' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'departure_date' => [
                'type' => 'DATE',
            ],
            'return_date' => [
                'type' => 'DATE',
            ],
            'duration_days' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'total_budget' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'signatory_ppk_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'signatory_kpa_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'pending', 'approved', 'verified', 'rejected', 'cancelled'],
                'default'    => 'draft',
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('no_surat_tugas');
        $this->forge->addUniqueKey('no_sppd');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('signatory_ppk_id', 'signatories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('signatory_kpa_id', 'signatories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('travel_requests');
    }

    public function down()
    {
        $this->forge->dropTable('travel_requests', true);
    }
}
