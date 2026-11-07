<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'member_code' => 'MEM-'.now()->year.'-'.str_pad((string) fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'title' => fake()->randomElement(['ኣቶ', 'ወይዘሮ']),
            'given_name' => fake()->firstName(),
            'family_name' => fake()->lastName(),
            'display_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'joined_at' => fake()->date(),
            'status' => fake()->randomElement(['active', 'inactive', 'former']),
            'restricted_notes' => fake()->optional()->sentence(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}