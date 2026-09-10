<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAISettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Byte-exact snapshot of the real .env taken before any test runs.
     *
     * These tests exercise AIController::update() which writes provider keys
     * (AI_PROVIDER, AI_MODEL_DEFAULT, provider flags, …) straight into
     * base_path('.env'). Without isolation, running the suite silently resets
     * the operator's configured default AI provider back to the baseline
     * (`gemini`) — the config-reset bug. Every test restores the original
     * file so the development/production configuration is never mutated.
     */
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

    public function test_admin_can_save_ai_settings_from_form_payload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            // providers tab
            'ai_provider' => 'gemini',
            'ai_gemini_enabled' => '1',
            'ai_gemini_api_key' => '',
            'ai_gemini_model' => 'gemini-3.6-flash',
            'ai_openai_enabled' => '0',
            'ai_openai_api_key' => '',
            'ai_openai_model' => 'gpt-4o',
            'ai_claude_enabled' => '0',
            'ai_claude_api_key' => '',
            'ai_claude_model' => 'claude-3-5-sonnet',
            'ai_ollama_enabled' => '0',
            'ai_ollama_url' => 'http://localhost:11434',
            'ai_ollama_model' => 'llama3',
            // routing tab
            'ai_model_default' => 'gemini-test-model-x',
            'ai_model_pre_observation_provider' => '',
            'ai_model_pre_observation' => 'gemini-3.6-flash',
            'ai_model_observation_guidance_provider' => '',
            'ai_model_observation_guidance' => 'gemini-3.6-flash',
            'ai_model_feedback_provider' => '',
            'ai_model_feedback' => 'gemini-3.6-flash',
            'ai_model_post_conference_provider' => '',
            'ai_model_post_conference' => 'gemini-3.6-flash',
            'ai_model_final_report_provider' => '',
            'ai_model_final_report' => 'gemini-3.6-flash',
            // general tab
            'ai_enabled' => '1',
            'ai_fallback_enabled' => '1',
            'ai_logging_enabled' => '1',
            'ai_python_bridge_enabled' => '0',
            // bypass the live connectivity check in tests
            'ai_skip_verify' => '1',
        ];

        $response = $this->actingAs($admin)->post(route('admin.ai.update'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $env = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('AI_MODEL_DEFAULT=gemini-test-model-x', $env);
    }

    public function test_changed_default_provider_persists_in_env_and_runtime_config(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.ai.update'), [
            'ai_provider' => 'openai',
            'ai_model_default' => 'gpt-4o',
            'ai_gemini_enabled' => '1',
            'ai_gemini_api_key' => '',
            'ai_gemini_model' => 'gemini-3.6-flash',
            'ai_skip_verify' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Persisted to the env file so a fresh process (e.g. after a system
        // restart) boots the chosen provider rather than the baseline.
        $env = file_get_contents(base_path('.env'));
        $this->assertMatchesRegularExpression('/^AI_PROVIDER=openai$/m', $env);
        $this->assertMatchesRegularExpression('/^AI_MODEL_DEFAULT=gpt-4o$/m', $env);

        // Reflected in the running process so the admin page immediately shows
        // the new selection instead of a stale value.
        $this->assertSame('openai', config('ai.provider'));
        $this->assertSame('gpt-4o', config('ai.models.default'));
    }

    public function test_custom_model_option_writes_custom_value(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.ai.update'), [
            'ai_provider' => 'gemini',
            'ai_claude_enabled' => '1',
            'ai_claude_api_key' => '',
            'ai_claude_model' => '__custom__',
            'ai_claude_model_custom' => 'my-custom-claude-model',
            'ai_gemini_enabled' => '1',
            'ai_gemini_api_key' => '',
            'ai_gemini_model' => 'gemini-3.6-flash',
            'ai_skip_verify' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $env = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('CLAUDE_MODEL=my-custom-claude-model', $env);
        $this->assertStringNotContainsString('CLAUDE_MODEL=__custom__', $env);
    }

    public function test_custom_model_without_value_keeps_existing_config(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $before = file_get_contents(base_path('.env'));
        preg_match('/^GEMINI_MODEL=(.*)$/m', $before, $m);
        $existingModel = trim($m[1] ?? '');

        $this->assertNotEquals('', $existingModel, 'GEMINI_MODEL must be set before the request.');

        $response = $this->actingAs($admin)->post(route('admin.ai.update'), [
            'ai_provider' => 'gemini',
            'ai_gemini_enabled' => '1',
            'ai_gemini_api_key' => '',
            'ai_gemini_model' => '__custom__',
            // no ai_gemini_model_custom submitted (left empty)
            'ai_skip_verify' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $after = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('GEMINI_MODEL='.$existingModel, $after);
    }

    public function test_save_button_renders_inside_main_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.ai.index'));

        $response->assertOk();
        $html = $response->getContent();

        // No nested <form> tags: the main settings form must not be closed
        // before the Save button.
        $updateAction = 'action="' . route('admin.ai.update') . '"';
        $mainFormStart = strpos($html, $updateAction);
        $this->assertNotFalse($mainFormStart, 'Main AI settings form is missing.');

        $saveButtonPos = strpos($html, 'Save Configuration');
        $this->assertNotFalse($saveButtonPos, 'Save Configuration button is missing.');

        $firstCloseAfterMain = strpos($html, '</form>', $mainFormStart);
        $this->assertGreaterThan($saveButtonPos, $firstCloseAfterMain,
            'The main form closes before the Save button (nested form bug).');
    }

    public function test_model_fields_render_as_friendly_dropdowns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.ai.index'));
        $response->assertOk();

        $html = $response->getContent();

        // Provider card dropdowns exist with curated options.
        $this->assertStringContainsString('name="ai_gemini_model"', $html);
        $this->assertStringContainsString('Gemini 3.6 Flash — latest, fast &amp; reliable (recommended)', $html);

        // Default model picker is filtered to the current default provider's
        // catalog (OpenRouter lists MiniMax when OpenRouter is active), so the
        // page no longer shows one giant optgroup list of every provider.
        $this->assertStringContainsString('name="ai_model_default"', $html);
        $this->assertStringContainsString('value="gemini-3.6-flash"', $html);
        $this->assertStringNotContainsString('optgroup', $html);
        $this->assertStringContainsString('Custom model', $html);
    }

    public function test_emergency_toggle_disables_and_reenables_ai(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.ai.emergency'), ['action' => 'disable']);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $env = file_get_contents(base_path('.env'));
        $this->assertMatchesRegularExpression('/^AI_ENABLED=false$/m', $env);
        $this->assertMatchesRegularExpression('/^AI_FALLBACK_ENABLED=true$/m', $env);

        $response = $this->actingAs($admin)->post(route('admin.ai.emergency'), ['action' => 'enable']);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $env = file_get_contents(base_path('.env'));
        $this->assertMatchesRegularExpression('/^AI_ENABLED=true$/m', $env);
    }

    public function test_restore_reverts_to_last_backup(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $envFile = base_path('.env');
        $before = file_get_contents($envFile);

        $dir = storage_path('app/ai/env-backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        copy($envFile, $dir.'/ai-test-restore-'.now()->format('YmdHis-u').'.env');

        file_put_contents($envFile, preg_replace('/^AI_MODEL_DEFAULT=.*$/m', 'AI_MODEL_DEFAULT=broken-model-x', $before));

        $response = $this->actingAs($admin)->post(route('admin.ai.restore'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame($before, file_get_contents($envFile));
    }
}
