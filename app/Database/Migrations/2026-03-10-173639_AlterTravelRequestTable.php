<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTravelRequestTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('travel_requests', [
            'destination' => [
                'name' => 'precautions',
                'type' => 'TEXT',
                'null' => true,
            ],
            'origin' => [
                'name' => 'task_detail',
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addColumn('travel_requests', [
            'budget_burden_by' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('travel_requests', 'budget_burden_by');

        $this->forge->modifyColumn('travel_requests', [
            'precautions' => [
                'name'       => 'destination',
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'task_detail' => [
                'name'       => 'origin',
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
        ]);
    }
}
