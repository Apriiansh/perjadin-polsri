<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Revisi besar schema v2 berdasarkan meeting Wadir 1 (09/03/26).
 *
 * Changes:
 * 1. travel_requests: hapus employee_id, signatory_ppk_id, signatory_kpa_id, rejection_reason
 *    - Tambah surat_dasar, created_by
 *    - Ubah status ENUM → draft,active,completed,cancelled
 *    - no_surat_tugas, tgl_surat_tugas, destination_city, budget_burden_by jadi NOT NULL (required)
 *    - no_sppd, tgl_sppd, mak tetap nullable
 * 2. signatories: role_type ENUM → jabatan VARCHAR (free-text input)
 * 3. travel_completeness: tambah kolom upload & verifikasi
 * 4. CREATE travel_members (bridge: travel_requests ↔ employees, holds per-member SPPD)
 * 5. travel_expenses: ganti FK travel_request_id+employee_id → travel_member_id
 * 6. DROP travel_documents (merged ke travel_completeness)
 */
class ReviseSchemaV2 extends Migration
{
    public function up()
    {
        // ──────────────────────────────────────────────
        // 1. ALTER travel_requests
        // ──────────────────────────────────────────────

        // Drop foreign keys first
        $this->db->query('ALTER TABLE travel_requests DROP FOREIGN KEY IF EXISTS travel_requests_employee_id_foreign');
        $this->db->query('ALTER TABLE travel_requests DROP FOREIGN KEY IF EXISTS travel_requests_signatory_ppk_id_foreign');
        $this->db->query('ALTER TABLE travel_requests DROP FOREIGN KEY IF EXISTS travel_requests_signatory_kpa_id_foreign');

        // Drop old columns (no_sppd & tgl_sppd tetap di travel_requests)
        $this->forge->dropColumn('travel_requests', 'employee_id');
        $this->forge->dropColumn('travel_requests', 'signatory_ppk_id');
        $this->forge->dropColumn('travel_requests', 'signatory_kpa_id');
        $this->forge->dropColumn('travel_requests', 'rejection_reason');

        // Add new columns
        $this->forge->addColumn('travel_requests', [
            'surat_dasar' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'tgl_sppd',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status',
            ],
        ]);

        // Make previously-optional fields required
        $this->forge->modifyColumn('travel_requests', [
            'no_surat_tugas' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'tgl_surat_tugas' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'destination_city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'budget_burden_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
        ]);

        // Change status ENUM
        $this->db->query("ALTER TABLE travel_requests MODIFY COLUMN `status` ENUM('draft','active','completed','cancelled') NOT NULL DEFAULT 'draft'");

        // ──────────────────────────────────────────────
        // 2. ALTER signatories: role_type → jabatan
        // ──────────────────────────────────────────────
        $this->db->query("ALTER TABLE signatories CHANGE COLUMN `role_type` `jabatan` VARCHAR(150) NOT NULL");

        // ──────────────────────────────────────────────
        // 3. ALTER travel_completeness: add upload & verification columns
        // ──────────────────────────────────────────────
        $this->forge->addColumn('travel_completeness', [
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'document_path',
            ],
            'file_size' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'original_name',
            ],
            'uploaded_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'file_size',
            ],
            'uploaded_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'uploaded_by',
            ],
            'verified_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status',
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'verified_by',
            ],
            'verification_note' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'verified_at',
            ],
        ]);

        // Add 'rejected' to status ENUM
        $this->db->query("ALTER TABLE travel_completeness MODIFY COLUMN `status` ENUM('pending','uploaded','verified','rejected') NOT NULL DEFAULT 'pending'");

        // ──────────────────────────────────────────────
        // 4. CREATE travel_members
        // ──────────────────────────────────────────────
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
            'employee_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'no_sppd' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tgl_sppd' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('travel_members');

        // ──────────────────────────────────────────────
        // 5. Migrate travel_expenses data → travel_members, then alter travel_expenses
        // ──────────────────────────────────────────────

        // Step 5a: Copy existing travel_expenses rows into travel_members
        $this->db->query("
            INSERT INTO travel_members (travel_request_id, employee_id, created_at, updated_at)
            SELECT DISTINCT travel_request_id, employee_id, NOW(), NOW()
            FROM travel_expenses
        ");

        // Step 5b: Add travel_member_id column to travel_expenses
        $this->forge->addColumn('travel_expenses', [
            'travel_member_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        // Step 5c: Populate travel_member_id from the newly created travel_members
        $this->db->query("
            UPDATE travel_expenses te
            INNER JOIN travel_members tm
                ON tm.travel_request_id = te.travel_request_id
                AND tm.employee_id = te.employee_id
            SET te.travel_member_id = tm.id
        ");

        // Step 5d: Drop old FKs and columns from travel_expenses
        $this->db->query('ALTER TABLE travel_expenses DROP FOREIGN KEY IF EXISTS travel_expenses_travel_request_id_foreign');
        $this->db->query('ALTER TABLE travel_expenses DROP FOREIGN KEY IF EXISTS travel_expenses_employee_id_foreign');

        $this->forge->dropColumn('travel_expenses', 'travel_request_id');
        $this->forge->dropColumn('travel_expenses', 'employee_id');

        // Step 5e: Make travel_member_id NOT NULL and add FK
        $this->forge->modifyColumn('travel_expenses', [
            'travel_member_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->db->query('ALTER TABLE travel_expenses ADD CONSTRAINT travel_expenses_travel_member_id_foreign FOREIGN KEY (travel_member_id) REFERENCES travel_members(id) ON DELETE CASCADE ON UPDATE CASCADE');

        // ──────────────────────────────────────────────
        // 6. DROP travel_documents
        // ──────────────────────────────────────────────
        $this->forge->dropTable('travel_documents', true);
    }

    public function down()
    {
        // This is a major restructure — a full down() is complex.
        // Provide basic reverse for development use only.

        // Re-create travel_documents
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'travel_expense_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'document_type' => [
                'type'       => 'ENUM',
                'constraint' => ['nota_tiket', 'nota_hotel', 'boarding_pass', 'nota_lainnya'],
            ],
            'file_path'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_size'     => ['type' => 'INT', 'constraint' => 11],
            'is_verified'   => ['type' => 'BOOLEAN', 'default' => false],
            'verification_note' => ['type' => 'TEXT', 'null' => true],
            'verified_by'   => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'verified_at'   => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('travel_documents');

        // Reverse travel_expenses: re-add travel_request_id & employee_id
        $this->db->query('ALTER TABLE travel_expenses DROP FOREIGN KEY IF EXISTS travel_expenses_travel_member_id_foreign');

        $this->forge->addColumn('travel_expenses', [
            'travel_request_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true, 'after' => 'id'],
            'employee_id'       => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true, 'after' => 'travel_request_id'],
        ]);

        // Copy data back from travel_members
        $this->db->query("
            UPDATE travel_expenses te
            INNER JOIN travel_members tm ON tm.id = te.travel_member_id
            SET te.travel_request_id = tm.travel_request_id,
                te.employee_id = tm.employee_id
        ");

        $this->forge->dropColumn('travel_expenses', 'travel_member_id');

        // Drop travel_members
        $this->forge->dropTable('travel_members', true);

        // Remove new columns from travel_completeness
        $this->forge->dropColumn('travel_completeness', ['original_name', 'file_size', 'uploaded_by', 'uploaded_at', 'verified_by', 'verified_at', 'verification_note']);
        $this->db->query("ALTER TABLE travel_completeness MODIFY COLUMN `status` ENUM('pending','uploaded','verified') NOT NULL DEFAULT 'pending'");

        // Reverse signatories
        $this->db->query("ALTER TABLE signatories CHANGE COLUMN `jabatan` `role_type` ENUM('PPK','KPA','Bendahara','Kepala_Keuangan') NOT NULL");

        // Reverse travel_requests
        $this->forge->dropColumn('travel_requests', ['surat_dasar', 'created_by']);

        $this->forge->addColumn('travel_requests', [
            'employee_id'      => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true, 'after' => 'id'],
            'signatory_ppk_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'signatory_kpa_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'rejection_reason' => ['type' => 'TEXT', 'null' => true],
        ]);

        $this->db->query("ALTER TABLE travel_requests MODIFY COLUMN `status` ENUM('draft','pending','approved','verified','rejected','cancelled') NOT NULL DEFAULT 'draft'");

        // Revert required fields back to nullable
        $this->forge->modifyColumn('travel_requests', [
            'no_surat_tugas'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tgl_surat_tugas'  => ['type' => 'DATE', 'null' => true],
            'destination_city' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'budget_burden_by' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
    }
}
