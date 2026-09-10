<?php

namespace Database\Factories;

use App\Models\CustomAiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomAiProvider>
 */
class CustomAiProviderFactory extends Factory
{
    protected $model = CustomAiProvider::class;

    public function definition(): array
    {
        return [
            'slug' => Str::slug(fake()->unique()->company),
            'name' => fake()->company(),
            'base_url' => 'https://api.example.com/openai/v1',
            'api_key' => 'sk-'.Str::random(32),
            'models' => [
                ['id' => 'default-chat', 'name' => 'Default Chat'],
            ],
            'default_model' => 'default-chat',
            'enabled' => true,
        ];
    }
}