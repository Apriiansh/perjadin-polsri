<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyTransportationTypeEnum extends Migration
{
    public function up()
    {
        // Alter the ENUM column to include 'udara' instead of 'pesawat'
        $this->db->query("ALTER TABLE travel_requests MODIFY COLUMN transportation_type ENUM('udara', 'darat', 'laut') NULL");
        
        // Update existing records from 'pesawat' to 'udara' if any existed before the schema strict change
        $this->db->query("UPDATE travel_requests SET transportation_type = 'udara' WHERE transportation_type = 'pesawat'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE travel_requests MODIFY COLUMN transportation_type ENUM('pesawat', 'darat', 'laut') NULL");
    }
}
