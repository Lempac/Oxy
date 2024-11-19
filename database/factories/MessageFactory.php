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
        return [
            'type' => $type,
            'mdata' => $type == MessageType::Text->value ? fake()->text(maxNbChars: 100) : ($type == MessageType::File->value ? 'Image'.'|*|'.'https://picsum.photos/'.random_int(120, 1920).'/'.random_int(120, 1080) : 'https://picsum.photos/'.random_int(120, 1920).'/'.random_int(120, 1080)),
        ];
    }
}
