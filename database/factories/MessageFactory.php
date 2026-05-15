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

        $imageUrl = 'https://picsum.photos/'.random_int(120, 1920).'/'.random_int(120, 1080);

        return [
            'type' => $type,
            'mdata' => match ($type) {
                MessageType::Text->value => fake()->text(maxNbChars: 100),
                MessageType::File->value => 'Image'.'|*|'.$imageUrl,
                MessageType::Image->value => $imageUrl,
                default => fake()->text(maxNbChars: 100),
            },
        ];
    }
}
