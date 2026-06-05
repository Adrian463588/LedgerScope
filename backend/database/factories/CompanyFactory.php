<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
final class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'legal_name' => $this->faker->company().' PT',
            'currency' => 'IDR',
            'fiscal_year_start_month' => 1,
            'city' => $this->faker->city(),
            'country' => 'Indonesia',
            'is_active' => true,
        ];
    }
}
