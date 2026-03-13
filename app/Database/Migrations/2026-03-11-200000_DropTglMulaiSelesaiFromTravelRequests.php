<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropTglMulaiSelesaiFromTravelRequests extends Migration
{
    public function up(): void
    {
        // Copy data from tgl_mulai/tgl_selesai to departure_date/return_date if needed
        $this->db->query("
            UPDATE travel_requests
            SET departure_date = tgl_mulai,
                return_date = tgl_selesai
            WHERE tgl_mulai IS NOT NULL
              AND (departure_date IS NULL OR departure_date = '0000-00-00')
        ");

        $this->forge->dropColumn('travel_requests', 'tgl_mulai');
        $this->forge->dropColumn('travel_requests', 'tgl_selesai');
    }

    public function down(): void
    {
        $this->forge->addColumn('travel_requests', [
            'tgl_mulai' => [
                'type'    => 'DATE',
                'null'    => true,
                'after'   => 'tahun_anggaran',
            ],
            'tgl_selesai' => [
                'type'    => 'DATE',
                'null'    => true,
                'after'   => 'tgl_mulai',
            ],
        ]);

        // Restore data
        $this->db->query("
            UPDATE travel_requests
            SET tgl_mulai = departure_date,
                tgl_selesai = return_date
            WHERE departure_date IS NOT NULL
        ");
    }
}
