<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSignatories extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'          => 'BIGINT',
                'constraint'    => 20,
                'unsigned'      => true,
                'auto_increment'=> true,
            ],
            'role_type'         => [
                'type'          => 'ENUM',
                'constraint'    => ['PPK', 'KPA', 'Bendahara', 'Kepala_Keuangan'],
                'null'          => false,
            ],
            'employee_id' => [
                'type'          => 'BIGINT',
                'constraint'    => 20,
                'unsigned'      => true,
                'null'          => false
            ],
            'is_active' => [
                'type'          => 'BOOLEAN',
                'default'       => true
            ],
            'created_at' => [
                'type'          => 'DATETIME',
                'null'          => true,
            ],
            'updated_at' => [
                'type'          => 'DATETIME',
                'null'          => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('signatories');
    }

    public function down()
    {
        $this->forge->dropTable('signatories', true);
    }
}
