<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportNarrativeToTravelRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('travel_requests', [
            'report_narrative' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'lampiran_original_name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('travel_requests', 'report_narrative');
    }
}
