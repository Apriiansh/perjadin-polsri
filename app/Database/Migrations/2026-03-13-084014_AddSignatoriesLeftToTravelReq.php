<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSignatoriesLeftToTravelReq extends Migration
{
    public function up()
    {
         $this->forge->addColumn('travel_requests', [
            'bpp_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'kpa_id',
            ],
        ]);

        $this->forge->addForeignKey('bpp_id', 'signatories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('travel_requests');
    }

    public function down()
    {
        $this->forge->dropForeignKey('travel_requests', 'travel_requests_bpp_id_foreign');
        $this->forge->dropColumn('travel_requests', 'bpp_id');
    }
}
