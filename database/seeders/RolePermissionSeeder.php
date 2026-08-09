<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions grouped by the role that owns them, following the
     * "Hak Akses Pengguna" section of snos.md.
     */
    private const ROLE_PERMISSIONS = [
        'peserta' => [
            'manage-own-profile',
            'register-event',
            'make-payment',
            'upload-article',
            'view-review-results',
            'upload-revision',
            'download-loa',
            'view-schedule',
            'check-in',
            'fill-evaluation',
            'download-certificate',
        ],
        'admin' => [
            'manage-event',
            'manage-participants',
            'verify-payments',
            'review-articles-administration',
            'manage-reviewers',
            'manage-schedule',
            'manage-attendance',
            'manage-certificates',
            'generate-reports',
        ],
        'reviewer' => [
            'view-assigned-articles',
            'download-articles',
            'submit-review',
            'upload-review-result',
            'give-recommendation',
        ],
        'moderator' => [
            'view-session-schedule',
            'view-presenter-list',
            'record-attendance',
            'assess-presentation',
            'write-minutes',
        ],
        'pimpinan' => [
            'view-statistics-dashboard',
            'view-participant-count',
            'view-revenue',
            'view-article-count',
            'view-publication-results',
            'download-activity-report',
        ],
        'narasumber' => [
            'manage-own-profile',
            'view-schedule',
            'download-certificate',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ROLE_PERMISSIONS as $role => $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission);
            }
        }

        foreach (self::ROLE_PERMISSIONS as $role => $permissions) {
            Role::findOrCreate($role)->syncPermissions($permissions);
        }

        // Panitia/admin oversees the full workflow, so it gets every permission.
        Role::findByName('admin')->syncPermissions(Permission::all());
    }
}
