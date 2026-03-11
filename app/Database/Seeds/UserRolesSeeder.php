<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserRolesSeeder extends Seeder
{
    public function run()
    {
        $usersData = [
            [
                'username' => 'superadmin',
                'email' => 'superadmin@perjadin.test',
                'password' => 'Perjadin#123',
                'group' => 'superadmin',
                'employee' => [
                    'nip' => '197001011990011001',
                    'name' => 'Super Admin PERJADIN',
                    'tingkat_biaya' => 'A',
                ],
            ],
            [
                'username' => 'admin',
                'email' => 'admin@perjadin.test',
                'password' => 'Perjadin#123',
                'group' => 'admin',
                'employee' => [
                    'nip' => '197501011999021001',
                    'name' => 'Admin PERJADIN',
                    'tingkat_biaya' => 'B',
                ],
            ],
            [
                'username' => 'verificator',
                'email' => 'verificator@perjadin.test',
                'password' => 'Perjadin#123',
                'group' => 'verificator',
                'employee' => [
                    'nip' => '198001012005011001',
                    'name' => 'Verificator PERJADIN',
                    'tingkat_biaya' => 'B',
                ],
            ],
            [
                'username' => 'lecturer',
                'email' => 'lecturer@perjadin.test',
                'password' => 'Perjadin#123',
                'group' => 'lecturer',
                'employee' => [
                    'nip' => '198501012010011001',
                    'name' => 'Lecturer PERJADIN',
                    'tingkat_biaya' => 'C',
                ],
            ],
        ];

        foreach ($usersData as $item) {
            $userId = $this->upsertUser($item['username']);
            $this->upsertEmailIdentity($userId, $item['email'], $item['password']);
            $this->upsertGroup($userId, $item['group']);
            $this->upsertEmployee($userId, $item['email'], $item['employee']);
        }
    }

    private function upsertUser(string $username): int
    {
        $usersTable = $this->db->table('users');
        $now = date('Y-m-d H:i:s');

        $existing = $usersTable->where('username', $username)->get()->getRowArray();

        if ($existing !== null) {
            $usersTable->where('id', $existing['id'])->update([
                'active' => 1,
                'updated_at' => $now,
            ]);

            return (int) $existing['id'];
        }

        $usersTable->insert([
            'username' => $username,
            'active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function upsertEmailIdentity(int $userId, string $email, string $password): void
    {
        $identityTable = $this->db->table('auth_identities');
        $now = date('Y-m-d H:i:s');
        $passwordHash = service('passwords')->hash($password);

        $this->db->table('auth_identities')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->where("(secret = '' OR secret IS NULL)", null, false)
            ->delete();

        $existingBySecret = $identityTable
            ->where('type', 'email_password')
            ->where('secret', $email)
            ->get()
            ->getRowArray();

        if ($existingBySecret !== null) {
            $identityTable->where('id', $existingBySecret['id'])->update([
                'user_id' => $userId,
                'secret2' => $passwordHash,
                'updated_at' => $now,
            ]);

            return;
        }

        $identityTable->insert([
            'user_id' => $userId,
            'type' => 'email_password',
            'name' => null,
            'secret' => $email,
            'secret2' => $passwordHash,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertGroup(int $userId, string $group): void
    {
        $groupsTable = $this->db->table('auth_groups_users');
        $existing = $groupsTable
            ->where('user_id', $userId)
            ->where('group', $group)
            ->get()
            ->getRowArray();

        if ($existing !== null) {
            return;
        }

        $groupsTable->insert([
            'user_id' => $userId,
            'group' => $group,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array{nip: string, name: string, tingkat_biaya: string} $employee
     */
    private function upsertEmployee(int $userId, string $email, array $employee): void
    {
        $table = $this->db->table('employees');
        $now = date('Y-m-d H:i:s');

        $payload = [
            'user_id' => $userId,
            'api_employee_id' => null,
            'nip' => $employee['nip'],
            'name' => $employee['name'],
            'email' => $email,
            'pangkat_golongan' => null,
            'jabatan' => null,
            'tingkat_biaya' => $employee['tingkat_biaya'],
            'rekening_bank' => null,
            'status' => 'aktif',
            'synced_at' => null,
            'updated_at' => $now,
        ];

        $existing = $table->where('nip', $employee['nip'])->get()->getRowArray();

        if ($existing !== null) {
            $table->where('id', $existing['id'])->update($payload);
            return;
        }

        $payload['created_at'] = $now;
        $table->insert($payload);
    }
}
