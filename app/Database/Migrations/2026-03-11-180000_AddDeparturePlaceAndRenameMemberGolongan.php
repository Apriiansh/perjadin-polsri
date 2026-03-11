<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeparturePlaceAndRenameMemberGolongan extends Migration
{
    public function up(): void
    {
        // 1. Add departure_place to travel_requests
        $this->forge->addColumn('travel_requests', [
            'departure_place' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'lokasi',
            ],
        ]);

        // 2. Rename pangkat_golongan → kode_golongan in travel_members
        $fields = [
            'pangkat_golongan' => [
                'name'       => 'kode_golongan',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ];
        $this->forge->modifyColumn('travel_members', $fields);

        // 3. Add nama_golongan after kode_golongan in travel_members
        $this->forge->addColumn('travel_members', [
            'nama_golongan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'kode_golongan',
            ],
        ]);
    }

    public function down(): void
    {
        // Remove departure_place from travel_requests
        $this->forge->dropColumn('travel_requests', 'departure_place');

        // Remove nama_golongan from travel_members
        $this->forge->dropColumn('travel_members', 'nama_golongan');

        // Rename kode_golongan back to pangkat_golongan
        $fields = [
            'kode_golongan' => [
                'name'       => 'pangkat_golongan',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ];
        $this->forge->modifyColumn('travel_members', $fields);
    }
}
