<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ReviseDbSchemeRev3 extends Migration
{
    public function up()
    {
        // 1. Rename perihal_surat_rujukan -> perihal in travel_requests
        if ($this->db->fieldExists('perihal_surat_rujukan', 'travel_requests')) {
            $this->forge->modifyColumn('travel_requests', [
                'perihal_surat_rujukan' => [
                    'name' => 'perihal',
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }

        // 2. Change kode_golongan and nama_golongan to NOT NULL in travel_members
        $this->forge->modifyColumn('travel_members', [
            'kode_golongan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'nama_golongan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        // 1. Rename perihal -> perihal_surat_rujukan
        if ($this->db->fieldExists('perihal', 'travel_requests')) {
            $this->forge->modifyColumn('travel_requests', [
                'perihal' => [
                    'name' => 'perihal_surat_rujukan',
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }

        // 2. Change back to NULL
        $this->forge->modifyColumn('travel_members', [
            'kode_golongan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'nama_golongan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
        ]);
    }
}
