<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTravelExpensesRenameTiketPesawatToTiket extends Migration
{
    public function up()
    {
        $fields = [
            'tiket_pesawat' => [
                'name' => 'tiket',
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
        ];
        $this->forge->modifyColumn('travel_expenses', $fields);
    }

    public function down()
    {
        $fields = [
            'tiket' => [
                'name' => 'tiket_pesawat',
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
        ];
        $this->forge->modifyColumn('travel_expenses', $fields);
    }
}
