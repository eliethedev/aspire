<?php

namespace App\Models;

use App\AI\Providers\CustomOpenAIProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomAiProvider extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'name',
        'base_url',
        'api_key',
        'models',
        'default_model',
        'enabled',
    ];

    protected $casts = [
        'models' => 'array',
        'enabled' => 'boolean',
        'api_key' => 'encrypted',
    ];

    /**
     * Generate a concrete, OpenAI-compatible provider instance backed by this
     * row's base URL, API key and model.
     */
    public function resolveInstance(?string $model = null): CustomOpenAIProvider
    {
        $model = ($model === null || trim($model) === '') ? $this->default_model : $model;

        return new CustomOpenAIProvider(
            apiKey: $this->api_key,
            baseUrl: $this->base_url,
            providerName: $this->slug,
            model: (string) $model,
        );
    }

    /**
     * Masked view of the stored API key ("abcd****wxyz"); empty when unset.
     */
    public function getMaskedApiKeyAttribute(): string
    {
        $key = (string) $this->api_key;

        if ($key === '') {
            return '';
        }

        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }

        return substr($key, 0, 4).str_repeat('*', strlen($key) - 8).substr($key, -4);
    }

    public function modelsList(): array
    {
        return array_values((array) $this->models);
    }
}