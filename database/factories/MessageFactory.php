<?php

namespace Database\Factories;

use App\Enums\MessageType;
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
        $type = fake()->randomElement(array_column(MessageType::cases(), 'name'));

        //TODO: Add faker for images
//        if ($type === MessageType::Image) {
//            fake()->image('image.jpg');
//        }

        return [
            'type' => $type,
            'data' => $type == MessageType::Text ? fake()->text(maxNbChars: 100) : null
        ];
    }
}
