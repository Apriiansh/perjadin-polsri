<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSignatoriesToTravelRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('travel_requests', [
            'ppk_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'tahun_anggaran',
            ],
            'kpa_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'ppk_id',
            ],
            'bendahara_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'kpa_id',
            ],
        ]);

        $this->forge->addForeignKey('ppk_id', 'signatories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('kpa_id', 'signatories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('bendahara_id', 'signatories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('travel_requests');
    }

    public function down()
    {
        $this->forge->dropForeignKey('travel_requests', 'travel_requests_ppk_id_foreign');
        $this->forge->dropForeignKey('travel_requests', 'travel_requests_kpa_id_foreign');
        $this->forge->dropForeignKey('travel_requests', 'travel_requests_bendahara_id_foreign');
        $this->forge->dropColumn('travel_requests', ['ppk_id', 'kpa_id', 'bendahara_id']);
    }
}
