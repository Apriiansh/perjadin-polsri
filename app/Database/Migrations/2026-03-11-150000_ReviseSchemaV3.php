<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Revisi schema v3 — ST sebagai lampiran (bukan generate).
 *
 * Changes:
 * 1. travel_requests: DROP no_sppd, tgl_sppd (redundan — sudah di travel_members)
 * 2. travel_requests: DROP surat_dasar (diganti 4 kolom terstruktur)
 * 3. travel_requests: DROP precautions (junk — tidak ada di format surat)
 * 4. travel_requests: ADD 4 kolom surat rujukan terstruktur
 * 5. travel_requests: ADD tahun_anggaran, lokasi, lampiran_path, lampiran_original_name
 */
class ReviseSchemaV3 extends Migration
{
    public function up()
    {
        // ── DROP kolom yang tidak diperlukan ──
        // no_sppd & tgl_sppd: redundan, sudah per-member di travel_members
        // surat_dasar: diganti 4 kolom terstruktur
        // precautions: tidak ada di format surat manapun

        $dropCols = ['no_sppd', 'tgl_sppd', 'surat_dasar', 'precautions'];
        foreach ($dropCols as $col) {
            if ($this->db->fieldExists($col, 'travel_requests')) {
                $this->forge->dropColumn('travel_requests', $col);
            }
        }

        // Also drop origin & destination if they still exist (legacy, unused)
        foreach (['origin', 'destination'] as $col) {
            if ($this->db->fieldExists($col, 'travel_requests')) {
                $this->forge->dropColumn('travel_requests', $col);
            }
        }

        // ── ADD kolom baru ──
        $this->forge->addColumn('travel_requests', [
            // Surat rujukan (terstruktur)
            'nomor_surat_rujukan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'tgl_surat_tugas',
            ],
            'tgl_surat_rujukan' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'nomor_surat_rujukan',
            ],
            'instansi_pengirim_rujukan' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'after'      => 'tgl_surat_rujukan',
            ],
            'perihal' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'instansi_pengirim_rujukan',
            ],

            // Lokasi & tahun anggaran
            'lokasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'destination_city',
            ],
            'tahun_anggaran' => [
                'type'       => 'YEAR',
                'null'       => true,
                'after'      => 'budget_burden_by',
            ],

            // Lampiran file ST
            'lampiran_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'total_budget',
            ],
            'lampiran_original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'lampiran_path',
            ],
        ]);
    }

    public function down()
    {
        // Drop new columns
        $newCols = [
            'nomor_surat_rujukan', 'tgl_surat_rujukan',
            'instansi_pengirim_rujukan', 'perihal_surat_rujukan',
            'lokasi', 'tahun_anggaran',
            'lampiran_path', 'lampiran_original_name',
        ];
        foreach ($newCols as $col) {
            if ($this->db->fieldExists($col, 'travel_requests')) {
                $this->forge->dropColumn('travel_requests', $col);
            }
        }

        // Re-add dropped columns
        $this->forge->addColumn('travel_requests', [
            'no_sppd' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'tgl_surat_tugas',
            ],
            'tgl_sppd' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'no_sppd',
            ],
            'surat_dasar' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'tgl_sppd',
            ],
            'precautions' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'task_detail',
            ],
        ]);
    }
}
