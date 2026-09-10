<?php

namespace Tests\Feature;

use App\AI\Providers\AIProviderManager;
use App\AI\Providers\CustomOpenAIProvider;
use App\AI\Providers\GeminiProvider;
use App\Models\CustomAiProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomAiProviderTest extends TestCase
{
    use RefreshDatabase;

    protected string $envSnapshot = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->envSnapshot = (string) file_get_contents(base_path('.env'));
    }

    protected function tearDown(): void
    {
        if ($this->envSnapshot !== '') {
            file_put_contents(base_path('.env'), $this->envSnapshot);
        }
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Groq AI',
            'base_url' => 'https://api.groq.com/openai/v1',
            'api_key' => 'gsk_secret-key-12345',
            'models' => "llama-3.3-70b-versatile|Llama 3.3 70B (versatile)\nllama-3.1-8b-instant",
            'default_model' => '',
            'enabled' => '1',
        ], $overrides);
    }

    public function test_admin_can_add_custom_provider(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.ai.providers.store'), $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_ai_providers', [
            'slug' => 'groq-ai',
            'name' => 'Groq AI',
            'base_url' => 'https://api.groq.com/openai/v1',
            'default_model' => 'llama-3.3-70b-versatile',
            'enabled' => true,
        ]);

        $provider = CustomAiProvider::where('slug', 'groq-ai')->firstOrFail();
        $this->assertSame('llama-3.3-70b-versatile', $provider->default_model);
        $this->assertSame([
            ['id' => 'llama-3.3-70b-versatile', 'name' => 'Llama 3.3 70B (versatile)'],
            ['id' => 'llama-3.1-8b-instant', 'name' => 'llama-3.1-8b-instant'],
        ], $provider->models);

        // API key stored encrypted — never in plain text.
        $this->assertNotSame('gsk_secret-key-12345', $provider->getRawOriginal('api_key'));
        $this->assertSame('gsk_secret-key-12345', (string) $provider->api_key);
    }

    public function test_duplicate_provider_name_gets_unique_slug(): void
    {
        CustomAiProvider::factory()->create(['slug' => 'groq-ai']);

        $response = $this->actingAs($this->admin())->post(route('admin.ai.providers.store'), $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_ai_providers', ['slug' => 'groq-ai-2']);
    }

    public function test_store_requires_at_least_one_model(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.ai.providers.store'), $this->validPayload([
            'models' => "\n  \n",
        ]));

        $response->assertSessionHasErrors('models');
        $this->assertDatabaseCount('custom_ai_providers', 0);
    }

    public function test_update_keeps_api_key_when_blank_and_reslugs_on_rename(): void
    {
        $provider = CustomAiProvider::factory()->create([
            'slug' => 'groq-ai',
            'name' => 'Groq AI',
            'api_key' => 'gsk_original',
            'base_url' => 'https://api.groq.com/openai/v1',
            'models' => [['id' => 'llama-3.3-70b-versatile', 'name' => 'Llama 3.3 70B (versatile)']],
            'default_model' => 'llama-3.3-70b-versatile',
            'enabled' => true,
        ]);

        $response = $this->actingAs($this->admin())->post(route('admin.ai.providers.update', $provider), $this->validPayload([
            'name' => 'Groq Cloud',
            'api_key' => '', // keep existing
            'models' => "llama-3.3-70b-versatile|Llama 3.3 70B (versatile)\nllama-3.1-8b-instant|Llama 3.1 8B (instant)",
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $provider->refresh();
        $this->assertSame('groq-cloud', $provider->slug);
        $this->assertSame('Groq Cloud', $provider->name);
        $this->assertSame('gsk_original', (string) $provider->api_key);
        $this->assertCount(2, $provider->models);
    }

    public function test_rename_of_active_provider_rewrites_env_reference(): void
    {
        $provider = CustomAiProvider::factory()->create([
            'slug' => 'groq-ai',
            'name' => 'Groq AI',
            'api_key' => 'gsk_secret',
            'base_url' => 'https://api.groq.com/openai/v1',
            'models' => [['id' => 'llama-3.3-70b-versatile', 'name' => 'Llama 3.3 70B (versatile)']],
            'default_model' => 'llama-3.3-70b-versatile',
        ]);
        config(['ai.provider' => 'groq-ai']);

        $this->actingAs($this->admin())->post(route('admin.ai.providers.update', $provider), $this->validPayload([
            'name' => 'Groq Cloud',
            'api_key' => '',
        ]));

        $env = file_get_contents(base_path('.env'));
        $this->assertMatchesRegularExpression('/^AI_PROVIDER=groq-cloud$/m', $env);
        $this->assertSame('groq-cloud', config('ai.provider'));
    }

    public function test_cannot_delete_active_default_provider(): void
    {
        $provider = CustomAiProvider::factory()->create([
            'slug' => 'groq-ai',
            'name' => 'Groq AI',
            'api_key' => 'gsk_secret',
        ]);
        config(['ai.provider' => 'groq-ai']);

        $response = $this->actingAs($this->admin())->delete(route('admin.ai.providers.destroy', $provider));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('custom_ai_providers', ['id' => $provider->id]);
    }

    public function test_can_delete_inactive_provider(): void
    {
        $provider = CustomAiProvider::factory()->create(['slug' => 'groq-ai', 'name' => 'Groq AI']);

        $response = $this->actingAs($this->admin())->delete(route('admin.ai.providers.destroy', $provider));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('custom_ai_providers', ['id' => $provider->id]);
    }

    public function test_default_provider_validation_accepts_custom_slugs(): void
    {
        $provider = CustomAiProvider::factory()->create([
            'slug' => 'groq-ai',
            'name' => 'Groq AI',
            'api_key' => 'gsk_secret',
            'base_url' => 'https://api.groq.com/openai/v1',
            'models' => [['id' => 'llama-3.3-70b-versatile', 'name' => 'Llama 3.3 70B (versatile)']],
            'default_model' => 'llama-3.3-70b-versatile',
        ]);

        $response = $this->actingAs($this->admin())->post(route('admin.ai.update'), [
            'ai_provider' => $provider->slug,
            'ai_model_default' => 'llama-3.3-70b-versatile',
            'ai_gemini_enabled' => '1',
            'ai_gemini_api_key' => '',
            'ai_gemini_model' => 'gemini-3.6-flash',
            'ai_skip_verify' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $env = file_get_contents(base_path('.env'));
        $this->assertMatchesRegularExpression('/^AI_PROVIDER=groq-ai$/m', $env);
        $this->assertSame('groq-ai', config('ai.provider'));
    }

    public function test_index_page_lists_custom_provider_as_default_option_with_models(): void
    {
        CustomAiProvider::factory()->create([
            'slug' => 'groq-ai',
            'name' => 'Groq AI',
            'api_key' => 'gsk_secret',
            'base_url' => 'https://api.groq.com/openai/v1',
            'models' => [['id' => 'llama-3.3-70b-versatile', 'name' => 'Llama 3.3 70B (versatile)']],
            'default_model' => 'llama-3.3-70b-versatile',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.ai.index'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('value="groq-ai"', $html);
        $this->assertStringContainsString('Groq AI (Custom)', $html);
        $this->assertStringContainsString('Groq AI', $html);
        $this->assertStringContainsString('https://api.groq.com/openai/v1', $html);
    }

    public function test_manager_resolves_custom_provider_as_openai_compatible_instance(): void
    {
        $provider = CustomAiProvider::factory()->create([
            'slug' => 'groq-ai',
            'name' => 'Groq AI',
            'api_key' => 'gsk_secret',
            'base_url' => 'https://api.groq.com/openai/v1',
            'models' => [['id' => 'llama-3.3-70b-versatile', 'name' => 'Llama 3.3 70B (versatile)']],
            'default_model' => 'llama-3.3-70b-versatile',
            'enabled' => true,
        ]);

        $instance = app(AIProviderManager::class)->createProvider('groq-ai');

        $this->assertInstanceOf(CustomOpenAIProvider::class, $instance);
        $this->assertSame('groq-ai', $instance->getProviderName());
        $this->assertSame('llama-3.3-70b-versatile', $instance->getModelName());
        $this->assertTrue($instance->isAvailable());

        $overridden = app(AIProviderManager::class)->createProvider('groq-ai', 'llama-3.1-8b-instant');
        $this->assertSame('llama-3.1-8b-instant', $overridden->getModelName());
    }

    public function test_manager_ignores_disabled_custom_provider_and_falls_back(): void
    {
        CustomAiProvider::factory()->create([
            'slug' => 'groq-ai',
            'name' => 'Groq AI',
            'api_key' => 'gsk_secret',
            'enabled' => false,
        ]);

        $instance = app(AIProviderManager::class)->createProvider('groq-ai');

        $this->assertInstanceOf(GeminiProvider::class, $instance);

        $unknown = app(AIProviderManager::class)->createProvider('no-such-provider');
        $this->assertInstanceOf(GeminiProvider::class, $unknown);
    }
}