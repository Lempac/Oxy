<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['text', 'image']);
        return [
            'type' => $type,
            'data' => $type == 'text' ? fake()->text(maxNbChars: 100) : null
        ];
    }
}
