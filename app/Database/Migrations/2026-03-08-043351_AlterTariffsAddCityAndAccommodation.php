<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTariffsAddCityAndAccommodation extends Migration
{
    public function up()
    {
        // 1. Tambahkan kolom city dan jenis_penginapan
        $fields = [
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'province'
            ],
            'jenis_penginapan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => 'Standar Hotel',
                'after'      => 'penginapan'
            ],
        ];

        $this->forge->addColumn('tariffs', $fields);

        // 2. Modifikasi Unik Key yang lama agar mencakup city dan jenis_penginapan
        // Hapus index lama
        $this->db->query('ALTER TABLE `tariffs` DROP INDEX `tariffs_unique_rate`');

        // Buat index baru yang lebih spesifik
        $this->db->query('ALTER TABLE `tariffs` ADD UNIQUE INDEX `tariffs_unique_rate` (`province`, `city`, `tingkat_biaya`, `jenis_penginapan`, `tahun_berlaku`)');
    }

    public function down()
    {
        // Kembalikan seperti semula
        $this->db->query('ALTER TABLE `tariffs` DROP INDEX `tariffs_unique_rate`');
        $this->db->query('ALTER TABLE `tariffs` ADD UNIQUE INDEX `tariffs_unique_rate` (`province`, `tingkat_biaya`, `tahun_berlaku`)');

        $this->forge->dropColumn('tariffs', ['city', 'jenis_penginapan']);
    }
}
