<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveEmailFromEmployees extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('employees', 'email');
    }

    public function down()
    {
        $this->forge->addColumn('employees', [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ]
        ]);
    }
}
