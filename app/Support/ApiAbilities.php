<?php

namespace App\Support;

class ApiAbilities
{
    public static function grouped(): array
    {
        return [
            'Full Access' => ['*' => 'Allow every API action'],
            'Users' => [
                'users:read' => 'View all users',
                'users:create' => 'Create users',
                'users:update' => 'Update users',
                'users:delete' => 'Delete users',
            ],
            'Teachers' => [
                'teachers:read' => 'View teachers',
                'teachers:create' => 'Create teachers',
                'teachers:update' => 'Update teachers',
                'teachers:delete' => 'Delete teachers',
            ],
            'Students' => [
                'students:read' => 'View students',
                'students:create' => 'Create students',
                'students:update' => 'Update students',
                'students:delete' => 'Delete students',
            ],
            'Classes' => [
                'classes:read' => 'View classes and sessions',
                'classes:create' => 'Create classes and sessions',
                'classes:update' => 'Update classes and sessions',
                'classes:delete' => 'Delete classes and sessions',
            ],
            'Plans' => [
                'plans:read' => 'View plans and sessions',
                'plans:create' => 'Create plans and sessions',
                'plans:update' => 'Update plans and sessions',
                'plans:delete' => 'Delete plans and sessions',
            ],
            'Class Cards' => [
                'classcards:read' => 'View class cards and purchases',
                'classcards:create' => 'Create class cards and purchases',
                'classcards:update' => 'Update class cards and purchases',
                'classcards:delete' => 'Delete class cards and purchases',
            ],
            'Attendance' => [
                'attendance:read' => 'View attendance data',
                'attendance:mark' => 'Mark attendance or class card usage',
                'attendance:update' => 'Update attendance records',
            ],
            'Commerce' => [
                'payments:read' => 'View payments',
                'orders:read' => 'View orders',
                'shop:read' => 'View shop products',
            ],
            'Notifications' => [
                'notifications:read' => 'View notifications',
                'notifications:create' => 'Create notifications',
                'notifications:update' => 'Update notifications',
                'notifications:delete' => 'Delete notifications',
            ],
            'Settings & Reports' => [
                'settings:read' => 'View studio settings',
                'settings:update' => 'Update studio settings',
                'reports:read' => 'View dashboard and reports data',
                'api-logs:read' => 'View API request logs',
            ],
        ];
    }

    public static function all(): array
    {
        return collect(self::grouped())->flatMap(fn (array $abilities) => array_keys($abilities))->values()->all();
    }

    public static function labels(): array
    {
        return collect(self::grouped())->flatMap(fn (array $abilities) => $abilities)->all();
    }
}
