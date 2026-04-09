<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentTravelCompletenessTables extends Migration
{
    public function up()
    {
        // 1. Student Travel Completeness Table
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
            'student_member_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'item_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'uploaded', 'verified', 'rejected'],
                'default'    => 'pending',
            ],
            'verified_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'verification_note' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addForeignKey('student_member_id', 'travel_student_members', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('travel_student_completeness');

        // 2. Student Travel Completeness Files Table
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'completeness_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_size' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'uploaded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('completeness_id', 'travel_student_completeness', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('travel_student_completeness_files');

        // 3. Add report_narrative to travel_student_members
        $this->forge->addColumn('travel_student_members', [
            'report_narrative' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'is_representative',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('travel_student_members', 'report_narrative');
        $this->forge->dropTable('travel_student_completeness_files', true);
        $this->forge->dropTable('travel_student_completeness', true);
    }
}
