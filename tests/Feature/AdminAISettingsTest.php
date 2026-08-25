<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAISettingsTest extends TestCase
{
    use RefreshDatabase;

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
        ];

        $response = $this->actingAs($admin)->post(route('admin.ai.update'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $env = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('AI_MODEL_DEFAULT=gemini-test-model-x', $env);
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

        $this->assertStringContainsString('GEMINI_MODEL=gemini-3.6-flash', $before);

        $response = $this->actingAs($admin)->post(route('admin.ai.update'), [
            'ai_provider' => 'gemini',
            'ai_gemini_enabled' => '1',
            'ai_gemini_api_key' => '',
            'ai_gemini_model' => '__custom__',
            // no ai_gemini_model_custom submitted (left empty)
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $after = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('GEMINI_MODEL=gemini-3.6-flash', $after);
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

        // Stage routing + default fallback use the grouped picker.
        $this->assertStringContainsString('name="ai_model_feedback"', $html);
        $this->assertStringContainsString('optgroup label="OpenAI"', $html);
        $this->assertStringContainsString('Custom model', $html);
    }
}
