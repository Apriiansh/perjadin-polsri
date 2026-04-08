<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HapusJabatanKPAnBPP extends Migration
{
    public function up()
    {
        // 1. Drop Foreign Keys in travel_requests explicitly
        $this->forge->dropForeignKey('travel_requests', 'travel_requests_kpa_id_foreign');
        $this->forge->dropForeignKey('travel_requests', 'travel_requests_bpp_id_foreign');

        // 2. Drop columns in travel_requests
        $this->forge->dropColumn('travel_requests', ['kpa_id', 'bpp_id']);

        // 3. Delete signatories with jabatan KPA or BPP
        // This is to "clean up" the master data as requested
        $this->db->table('signatories')
            ->groupStart()
                ->like('jabatan', 'Kuasa Pengguna Anggaran')
                ->orLike('jabatan', 'KPA')
                ->orLike('jabatan', 'Bendahara Pengeluaran Pembantu')
                ->orLike('jabatan', 'BPP')
            ->groupEnd()
            ->delete();
    }

    public function down()
    {
        $this->forge->addColumn('travel_requests', [
            'kpa_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'ppk_id',
            ],
            'bpp_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'kpa_id',
            ],
        ]);

        $this->forge->addForeignKey('kpa_id', 'signatories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('bpp_id', 'signatories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('travel_requests');
    }
}
