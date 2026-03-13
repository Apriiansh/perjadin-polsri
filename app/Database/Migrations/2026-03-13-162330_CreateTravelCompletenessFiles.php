<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTravelCompletenessFiles extends Migration
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
            'completeness_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
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
            'uploaded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('completeness_id', 'travel_completeness', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('travel_completeness_files');
    }

    public function down()
    {
        $this->forge->dropTable('travel_completeness_files');
    }
}
