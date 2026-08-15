<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seeders = [
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
        ];

        if (app()->environment(['local', 'testing'])) {
            $seeders[] = DemoDataSeeder::class;
        }

        $this->call($seeders);
    }
}
