<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Modules\Contacts\Models\Address;
use App\Modules\Contacts\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Contacts\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'addressable_type' => Contact::class,
            'addressable_id' => Contact::factory(),
            'label' => fake()->randomElement(['Main Office', 'Home', 'Branch', null]),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional(0.3)->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country_code' => 'ES',
            'latitude' => fake()->optional(0.5)->latitude(),
            'longitude' => fake()->optional(0.5)->longitude(),
            'is_primary' => false,
            'type' => fake()->randomElement(['main', 'billing', 'shipping', 'other']),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    public function billing(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'billing',
        ]);
    }

    public function shipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'shipping',
        ]);
    }
}
