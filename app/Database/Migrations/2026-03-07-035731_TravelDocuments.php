<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TravelDocuments extends Migration
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
            'travel_expense_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'document_type' => [
                'type'       => 'ENUM',
                'constraint' => ['nota_tiket', 'nota_hotel', 'boarding_pass', 'nota_lainnya'],
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_size' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'is_verified' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'verification_note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'verified_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'verified_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addForeignKey('travel_expense_id', 'travel_expenses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('verified_by', 'employees', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('travel_documents');
    }

    public function down()
    {
        $this->forge->dropTable('travel_documents', true);
    }
}
