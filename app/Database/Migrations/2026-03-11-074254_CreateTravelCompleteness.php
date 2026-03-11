<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTravelCompleteness extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'travel_request_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'item_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'e.g., Tiket Pesawat PP, Nota Hotel',
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['reimbursement', 'vendor', 'non_reimbursement'],
                'null'       => true,
            ],
            'remark' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'document_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'uploaded', 'verified'],
                'default'    => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('travel_request_id', 'travel_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('travel_completeness');
    }

    public function down()
    {
        $this->forge->dropTable('travel_completeness', true);
    }
}
