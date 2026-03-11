<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Tariffs extends Migration
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
            'province' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tingkat_biaya' => [
                'type'       => 'ENUM',
                'constraint' => ['A', 'B', 'C', 'D'],
            ],
            'uang_harian' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'uang_representasi' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'penginapan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tahun_berlaku' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addUniqueKey(['province', 'tingkat_biaya', 'tahun_berlaku'], 'tariffs_unique_rate');
        $this->forge->createTable('tariffs');
    }

    public function down()
    {
        $this->forge->dropTable('tariffs', true);
    }
}
