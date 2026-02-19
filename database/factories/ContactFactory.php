<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Modules\Contacts\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Contacts\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => 'person',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
            'source' => 'manual',
        ];
    }

    public function person(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'person',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'job_title' => fake()->jobTitle(),
        ]);
    }

    public function organization(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'organization',
            'name' => fake()->company(),
            'industry' => fake()->randomElement(['Technology', 'Finance', 'Healthcare', 'Manufacturing', 'Retail']),
            'first_name' => null,
            'last_name' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
