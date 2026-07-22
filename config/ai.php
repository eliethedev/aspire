<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable AI-powered features across the system.
    |
    */

    'enabled' => env('AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | AI Provider Selection
    |--------------------------------------------------------------------------
    |
    | The default provider used when no per-stage provider is specified.
    | Supported: gemini, openai, claude, ollama
    |
    */

    'provider' => env('AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Per-Stage Model Routing
    |--------------------------------------------------------------------------
    |
    | Configure which provider and model to use for each observation stage.
    | Each stage can target a different provider for cost/quality optimization.
    | If 'provider' is omitted, the default provider above is used.
    |
    */

    'models' => [
        'default' => env('AI_MODEL_DEFAULT', 'gemini-2.0-flash'),
        'pre_observation' => [
            'provider' => env('AI_MODEL_PRE_OBSERVATION_PROVIDER', ''),
            'model' => env('AI_MODEL_PRE_OBSERVATION', 'gemini-2.0-flash'),
        ],
        'observation_guidance' => [
            'provider' => env('AI_MODEL_OBSERVATION_GUIDANCE_PROVIDER', ''),
            'model' => env('AI_MODEL_OBSERVATION_GUIDANCE', 'gemini-2.0-flash'),
        ],
        'feedback' => [
            'provider' => env('AI_MODEL_FEEDBACK_PROVIDER', ''),
            'model' => env('AI_MODEL_FEEDBACK', 'gemini-2.0-flash'),
        ],
        'post_conference' => [
            'provider' => env('AI_MODEL_POST_CONFERENCE_PROVIDER', ''),
            'model' => env('AI_MODEL_POST_CONFERENCE', 'gemini-2.0-flash'),
        ],
        'final_report' => [
            'provider' => env('AI_MODEL_FINAL_REPORT_PROVIDER', ''),
            'model' => env('AI_MODEL_FINAL_REPORT', 'gemini-2.0-flash'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    |
    | API keys and settings for each supported AI provider.
    | API keys are also stored in config/services.php for provider classes.
    |
    */

    'providers' => [
        'gemini' => [
            'name' => 'Google Gemini',
            'enabled' => env('AI_GEMINI_ENABLED', true),
            'api_key' => env('GEMINI_API_KEY', ''),
            'default_model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
            'verify_ssl' => env('GEMINI_VERIFY_SSL', false),
        ],
        'openai' => [
            'name' => 'OpenAI',
            'enabled' => env('AI_OPENAI_ENABLED', false),
            'api_key' => env('OPENAI_API_KEY', ''),
            'default_model' => env('OPENAI_MODEL', 'gpt-4o'),
        ],
        'claude' => [
            'name' => 'Anthropic Claude',
            'enabled' => env('AI_CLAUDE_ENABLED', false),
            'api_key' => env('CLAUDE_API_KEY', ''),
            'default_model' => env('CLAUDE_MODEL', 'claude-sonnet-4-20250514'),
        ],
        'ollama' => [
            'name' => 'Ollama (Local)',
            'enabled' => env('AI_OLLAMA_ENABLED', false),
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
            'default_model' => env('OLLAMA_MODEL', 'llama3.1'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Generation Defaults
    |--------------------------------------------------------------------------
    |
    | Default temperature and token limits per AI task.
    |
    */

    'generation' => [
        'temperature' => 0.5,
        'max_output_tokens' => 1024,
        'timeout' => 30,
    ],

    'stages' => [
        'pre_observation' => [
            'temperature' => 0.5,
            'max_output_tokens' => 800,
        ],
        'observation_guidance' => [
            'temperature' => 0.4,
            'max_output_tokens' => 800,
        ],
        'feedback' => [
            'temperature' => 0.4,
            'max_output_tokens' => 1024,
        ],
        'post_conference' => [
            'temperature' => 0.5,
            'max_output_tokens' => 800,
        ],
        'final_report' => [
            'temperature' => 0.6,
            'max_output_tokens' => 2048,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Confidence Thresholds
    |--------------------------------------------------------------------------
    |
    | Thresholds for AI feedback confidence scoring.
    |
    */

    'confidence' => [
        'high' => 0.85,
        'medium' => 0.60,
        'low' => 0.40,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure AI usage logging for monitoring, cost tracking, and quality
    | assurance.
    |
    */

    'logging' => [
        'enabled' => env('AI_LOGGING_ENABLED', true),
        'channel' => env('AI_LOG_CHANNEL', 'stack'),
        'track_tokens' => env('AI_TRACK_TOKENS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Strategy
    |--------------------------------------------------------------------------
    |
    | When true, the system falls back to rule-based responses when AI is
    | unavailable or unconfigured.
    |
    */

    'fallback' => env('AI_FALLBACK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Which queue to dispatch AI jobs to.
    |
    */

    'queue' => env('AI_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Cache RAG data for performance.
    |
    */

    'cache' => [
        'ttl' => env('AI_CACHE_TTL', 3600),
        'key_prefix' => 'ai_rag_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Global per-user rate limits for AI features. "per_minute" applies to the
    | middleware-level limiter (broadest). "operations" let you define per-task
    | limits enforced in the service layer.
    |
    */

    'rate_limits' => [
        'per_minute' => (int) env('AI_RATE_LIMIT_PER_MINUTE', 30),
        'per_hour' => (int) env('AI_RATE_LIMIT_PER_HOUR', 200),
        'operations' => [
            'pre_observation' => ['limit' => 15, 'decay' => 60],
            'post_observation' => ['limit' => 10, 'decay' => 60],
            'post_conference' => ['limit' => 10, 'decay' => 60],
            'final_report' => ['limit' => 5,  'decay' => 300],
            'observation_guidance' => ['limit' => 20, 'decay' => 60],
            'feedback' => ['limit' => 20, 'decay' => 60],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Python AI Bridge
    |--------------------------------------------------------------------------
    |
    | Configuration for the Python-based AI orchestration bridge.
    | Used for advanced multi-model routing, fallback chains, and
    | batch processing that benefits from Python's AI ecosystem.
    |
    */

    'python_bridge' => [
        'enabled' => env('AI_PYTHON_BRIDGE_ENABLED', false),
        'script_path' => env('AI_PYTHON_BRIDGE_PATH', base_path('python/ai_bridge.py')),
        'python_path' => env('AI_PYTHON_PATH', 'python3'),
        'timeout' => env('AI_PYTHON_BRIDGE_TIMEOUT', 60),
    ],

];
