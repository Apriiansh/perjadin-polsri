<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'lecturer';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Keuangan',
            'description' => 'Full access and calculate honorarium.',
        ],
        'admin' => [
            'title'       => 'Kepegawaian',
            'description' => 'Input Surat Tugas and manage master data.',
        ],
        'verificator' => [
            'title'       => 'Verifikator (Keuangan)',
            'description' => 'Verify travel documents and completeness.',
        ],
        'lecturer' => [
            'title'       => 'Dosen',
            'description' => 'Travelers who upload documents and view ST/SPPD.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        // Travel requests
        'travel.create' => 'Can create travel requests',
        'travel.edit'   => 'Can edit travel requests',
        'travel.view'   => 'Can view travel requests',
        'travel.delete' => 'Can delete travel requests',

        // Verification
        'verification.verify' => 'Can verify travel documents',
        'verification.reject' => 'Can reject travel documents',

        // Master data
        'tariffs.manage'     => 'Can manage tariffs',
        'signatories.manage' => 'Can manage signatories',
        'employees.manage'   => 'Can manage employees',
        'reports.view'       => 'Can view reports',

        // Admin general
        'admin.access'        => 'Can access admin area',
        'admin.settings'      => 'Can access site settings',
        'users.manage-admins' => 'Can manage other admins',
        'users.create'        => 'Can create new users',
        'users.edit'          => 'Can edit existing users',
        'users.delete'        => 'Can delete existing users',

        // Beta features (opsional)
        'beta.access'         => 'Can access beta-level features',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin' => [
            '*',  // Semua permissions
        ],
        'admin' => [
            'admin.access',
            'tariffs.manage',
            'signatories.manage',
            'employees.manage',
            'reports.view',
            'travel.*',  // View, edit, delete semua travel
            'users.create',
            'users.edit',
            'users.delete',
        ],
        'verificator' => [
            'travel.view',
            'verification.verify',
            'verification.reject',
        ],
        'lecturer' => [
            'travel.create',
            'travel.edit',  // Hanya own
            'travel.view',  // Hanya own
        ],
    ];
}
