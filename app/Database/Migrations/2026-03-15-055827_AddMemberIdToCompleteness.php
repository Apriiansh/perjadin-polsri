<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMemberIdToCompleteness extends Migration
{
    public function up()
    {
        // 1. Add member_id to travel_completeness
        $this->forge->addColumn('travel_completeness', [
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'travel_request_id',
            ],
        ]);

        // 2. Add report_narrative to travel_members
        $this->forge->addColumn('travel_members', [
            'report_narrative' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'keterangan',
            ],
        ]);

        // 3. Remove report_narrative from travel_requests (Phase 27 redundant field)
        $this->forge->dropColumn('travel_requests', 'report_narrative');
    }

    public function down()
    {
        $this->forge->dropColumn('travel_completeness', 'member_id');
        $this->forge->dropColumn('travel_members', 'report_narrative');

        // Restore travel_requests report_narrative
        $this->forge->addColumn('travel_requests', [
            'report_narrative' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }
}
