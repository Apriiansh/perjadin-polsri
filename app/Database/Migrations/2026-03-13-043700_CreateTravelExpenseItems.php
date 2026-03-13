<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTravelExpenseItems extends Migration
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
            'travel_member_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['tiket', 'penginapan', 'transport_darat', 'transport_lokal', 'lain-lain'],
                'default'    => 'tiket',
            ],
            'item_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
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
        $this->forge->addForeignKey('travel_member_id', 'travel_members', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('travel_expense_items');
    }

    public function down()
    {
        $this->forge->dropTable('travel_expense_items');
    }
}
