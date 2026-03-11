<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTglMulaiTglSelesai extends Migration
{
    public function up()
    {
        $this->forge->addColumn('travel_requests', [
            'tgl_mulai' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'tahun_anggaran',
            ],
            'tgl_selesai' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'tgl_mulai',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('travel_requests', ['tgl_mulai', 'tgl_selesai']);
    }
}