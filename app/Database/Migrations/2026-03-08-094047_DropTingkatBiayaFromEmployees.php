<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropTingkatBiayaFromEmployees extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('employees', 'tingkat_biaya');
    }

    public function down()
    {
        $this->forge->addColumn('employees', [
            'tingkat_biaya' => [
                'type'       => 'VARCHAR',
                'constraint' => '5',
                'null'       => true,
            ]
        ]);
    }
}
