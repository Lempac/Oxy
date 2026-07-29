<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'status' => fake()->randomElement(UserStatus::cases())->value,
            'about_me' => fake()->optional(0.8)->paragraph(2),
            'remember_token' => Str::random(10),
        ];
    }
}
