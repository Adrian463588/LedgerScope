<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Common\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@ledgerscope.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@LedgerScope2026!'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        $superAdminRole = Role::where('name', 'super_admin')->first();

        if ($superAdminRole && ! $superAdmin->roles()->where('role_id', $superAdminRole->id)->exists()) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }
    }
}
