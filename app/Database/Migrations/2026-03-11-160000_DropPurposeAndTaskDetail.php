<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Drop purpose and task_detail columns from travel_requests.
 * These fields are redundant with perihal_surat_rujukan.
 */
class DropPurposeAndTaskDetail extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('travel_requests', 'purpose');
        $this->forge->dropColumn('travel_requests', 'task_detail');
    }

    public function down()
    {
        $this->forge->addColumn('travel_requests', [
            'purpose' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'mak',
            ],
            'task_detail' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'purpose',
            ],
        ]);
    }
}
