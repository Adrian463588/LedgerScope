<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $modules = ['journal', 'account', 'company', 'report', 'evidence', 'quarter'];
        $actions = ['view', 'create', 'update', 'delete', 'approve', 'post'];
        $module = fake()->randomElement($modules);
        $action = fake()->randomElement($actions);

        return [
            'name' => $module.'.'.$action.'.'.fake()->unique()->numerify('###'),
            'module' => $module,
            'action' => $action,
            'description' => fake()->sentence(),
        ];
    }
}
