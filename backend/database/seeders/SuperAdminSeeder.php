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
        $email = trim((string) env('LEDGERSCOPE_SUPERADMIN_EMAIL', ''));
        $password = (string) env('LEDGERSCOPE_SUPERADMIN_PASSWORD', '');

        if ($email === '' || $password === '') {
            return;
        }

        $superAdmin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('LEDGERSCOPE_SUPERADMIN_NAME', 'Super Admin'),
                'password' => Hash::make($password),
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
