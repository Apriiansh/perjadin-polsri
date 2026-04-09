<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryToTravelRequests extends Migration
{
    public function up()
    {
        $fields = [
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['pegawai', 'mahasiswa'],
                'default'    => 'pegawai',
                'after'      => 'created_by',
            ],
        ];
        $this->forge->addColumn('travel_requests', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('travel_requests', 'category');
    }
}
