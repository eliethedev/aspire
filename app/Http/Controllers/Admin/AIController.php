<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AIController extends Controller
{
    public function index()
    {
        $config = config('ai');

        $usageStats = [
            'total_calls' => AiUsageLog::count(),
            'successful' => AiUsageLog::where('success', true)->count(),
            'failed' => AiUsageLog::where('success', false)->count(),
            'success_rate' => $this->calculateSuccessRate(),
            'average_response_time' => number_format(AiUsageLog::where('success', true)->avg('response_time_ms') ?? 0, 0),
            'total_tokens' => (int) AiUsageLog::sum('total_tokens') ?: 0,
            'calls_by_stage' => AiUsageLog::selectRaw('stage, count(*) as total, sum(case when success then 1 else 0 end) as successful')
                ->groupBy('stage')
                ->get(),
            'recent_calls' => AiUsageLog::latest()->take(10)->get(),
        ];

        return view('admin.ai.index', compact('config', 'usageStats'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'ai_enabled' => 'boolean',
            'ai_fallback_enabled' => 'boolean',
            'ai_logging_enabled' => 'boolean',
            'ai_provider' => 'in:gemini',
            'ai_model_default' => 'string|max:100',
            'ai_model_pre_observation' => 'string|max:100',
            'ai_model_observation_guidance' => 'string|max:100',
            'ai_model_feedback' => 'string|max:100',
            'ai_model_post_conference' => 'string|max:100',
            'ai_model_final_report' => 'string|max:100',
            'ai_api_key' => 'nullable|string|max:255',
        ]);

        $envFile = base_path('.env');
        if (!file_exists($envFile)) {
            return back()->with('error', 'Environment file not found.');
        }

        $envContent = file_get_contents($envFile);

        $mappings = [
            'ai_enabled' => 'AI_ENABLED',
            'ai_fallback_enabled' => 'AI_FALLBACK_ENABLED',
            'ai_logging_enabled' => 'AI_LOGGING_ENABLED',
            'ai_provider' => 'AI_PROVIDER',
            'ai_model_default' => 'AI_MODEL_DEFAULT',
            'ai_model_pre_observation' => 'AI_MODEL_PRE_OBSERVATION',
            'ai_model_observation_guidance' => 'AI_MODEL_OBSERVATION_GUIDANCE',
            'ai_model_feedback' => 'AI_MODEL_FEEDBACK',
            'ai_model_post_conference' => 'AI_MODEL_POST_CONFERENCE',
            'ai_model_final_report' => 'AI_MODEL_FINAL_REPORT',
            'ai_api_key' => 'GOOGLE_GEMINI_API_KEY',
        ];

        foreach ($mappings as $field => $envKey) {
            if ($request->has($field)) {
                $value = $request->input($field);
                if ($field === 'ai_api_key' && empty($value)) {
                    continue;
                }
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $this->setEnvValue($envContent, $envKey, $value);
            }
        }

        file_put_contents($envFile, $envContent);

        Artisan::call('config:clear');

        return back()->with('success', 'AI settings updated successfully.');
    }

    public function test()
    {
        try {
            Artisan::call('ai:test', ['--no-interaction' => true]);
            $output = Artisan::output();

            if (str_contains($output, 'AI service is operational')) {
                return back()->with('success', 'AI connectivity test passed. ' . $output);
            }

            return back()->with('warning', 'AI test completed with issues: ' . $output);
        } catch (\Exception $e) {
            return back()->with('error', 'AI test failed: ' . $e->getMessage());
        }
    }

    private function calculateSuccessRate(): float
    {
        $total = AiUsageLog::count();
        if ($total === 0) {
            return 0;
        }
        $successful = AiUsageLog::where('success', true)->count();
        return round(($successful / $total) * 100, 1);
    }

    private function setEnvValue(string &$content, string $key, string $value): void
    {
        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$value}";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            $content .= PHP_EOL . $replacement;
        }
    }
}
