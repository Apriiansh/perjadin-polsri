<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentTravelTables extends Migration
{
    public function up()
    {
        // 1. Students Table
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nim'        => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'prodi'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'jurusan'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('students');

        // 2. Student Travel Members Table
        $this->forge->addField([
            'id'                => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'travel_request_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'student_id'        => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'jabatan'           => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'Anggota'],
            'is_representative' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('travel_request_id', 'travel_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('travel_student_members');

        // 3. Student Travel Expense Items Table
        $this->forge->addField([
            'id'                => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'travel_member_id'  => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'category'          => ['type' => 'ENUM', 'constraint' => ['pocket_money', 'transport', 'ticket', 'accommodation', 'other']],
            'item_name'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'amount'            => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('travel_member_id', 'travel_student_members', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('travel_student_expense_items');
    }

    public function down()
    {
        $this->forge->dropTable('travel_student_expense_items');
        $this->forge->dropTable('travel_student_members');
        $this->forge->dropTable('students');
    }
}
