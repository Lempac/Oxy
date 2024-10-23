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
        $type = fake()->randomElement(array_column(MessageType::cases(), 'value'));

        //TODO: Add faker for images
//        if ($type === MessageType::Image) {
//            fake()->image('image.jpg');
//        }
//        dd([$type, $type == MessageType::Text->value]);
        return [
            'type' => $type,
            'mdata' => $type == MessageType::Text->value ? fake()->text(maxNbChars: 100) : null
        ];
    }
}
