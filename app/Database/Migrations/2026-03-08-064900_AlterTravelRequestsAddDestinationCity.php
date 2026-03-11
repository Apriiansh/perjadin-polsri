<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTravelRequestsAddDestinationCity extends Migration
{
    public function up()
    {
        $this->forge->addColumn('travel_requests', [
            'destination_city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'destination_province'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('travel_requests', 'destination_city');
    }
}
