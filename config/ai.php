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
    | Model Catalog (Admin UI)
    |--------------------------------------------------------------------------
    |
    | Curated, human-readable model choices shown as dropdowns on the Admin
    | AI Settings page so administrators do not need to memorize exact
    | model IDs. Each entry: id (exact API model identifier) and name
    | (friendly display label). Picking "Custom model…" allows entering an
    | arbitrary ID not listed here.
    |
    */

    'model_catalog' => [
        'gemini' => [
            'label' => 'Google Gemini',
            'models' => [
                ['id' => 'gemini-3.6-flash', 'name' => 'Gemini 3.6 Flash — latest, fast & reliable (recommended)'],
                ['id' => 'gemini-3.5-flash', 'name' => 'Gemini 3.5 Flash — proven & stable'],
                ['id' => 'gemini-3.5-flash-lite', 'name' => 'Gemini 3.5 Flash-Lite — ultra-fast, low-cost'],
                ['id' => 'gemini-flash-latest', 'name' => 'Gemini Flash (Latest) — auto-updates to newest flash'],
                ['id' => 'gemini-3.1-pro-preview', 'name' => 'Gemini 3.1 Pro (Preview) — highest quality, slower'],
                ['id' => 'gemini-2.5-flash', 'name' => 'Gemini 2.5 Flash — older stable generation'],
                ['id' => 'gemini-2.5-pro', 'name' => 'Gemini 2.5 Pro — pro tier, stable'],
                ['id' => 'gemini-pro-latest', 'name' => 'Gemini Pro (Latest) — auto-updates to newest pro'],
            ],
        ],
        'openai' => [
            'label' => 'OpenAI',
            'models' => [
                ['id' => 'gpt-4o', 'name' => 'GPT-4o — balanced quality & speed (recommended)'],
                ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o mini — cheapest & fastest'],
                ['id' => 'gpt-4.1', 'name' => 'GPT-4.1 — high quality'],
                ['id' => 'gpt-4.1-mini', 'name' => 'GPT-4.1 mini — fast, newer generation'],
            ],
        ],
        'claude' => [
            'label' => 'Anthropic Claude',
            'models' => [
                ['id' => 'claude-sonnet-4-20250514', 'name' => 'Claude Sonnet 4 — balanced (recommended)'],
                ['id' => 'claude-3-7-sonnet-latest', 'name' => 'Claude 3.7 Sonnet — previous generation'],
                ['id' => 'claude-3-5-haiku-latest', 'name' => 'Claude 3.5 Haiku — fastest & cheapest'],
            ],
        ],
        'deepseek' => [
            'label' => 'DeepSeek',
            'models' => [
                ['id' => 'deepseek-chat', 'name' => 'DeepSeek Chat — general use (recommended)'],
                ['id' => 'deepseek-reasoner', 'name' => 'DeepSeek Reasoner — deeper step-by-step reasoning'],
            ],
        ],
        'openrouter' => [
            'label' => 'OpenRouter (Fallback)',
            'models' => [
                ['id' => 'nvidia/nemotron-3.5-lightning:free', 'name' => 'Nemotron 3.5 Lightning — fast free model (recommended)'],
                ['id' => 'meta-llama/llama-3.1-8b-instruct:free', 'name' => 'Llama 3.1 8B — Meta free model'],
                ['id' => 'qwen/qwen-2.5-72b-instruct:free', 'name' => 'Qwen 2.5 72B — strong free model'],
                ['id' => 'google/gemma-2-9b-it:free', 'name' => 'Gemma 2 9B — Google free model'],
                ['id' => 'minimax/minimax-m2.5', 'name' => 'MiniMax M2.5 — strong general model (paid, uses credits)'],
                ['id' => 'minimax/minimax-m2.7', 'name' => 'MiniMax M2.7 — latest productivity model (paid, uses credits)'],
                ['id' => 'minimax/minimax-m3', 'name' => 'MiniMax M3 — flagship, 1M context (paid, uses credits)'],
            ],
        ],
        'ollama' => [
            'label' => 'Ollama (Local)',
            'models' => [
                ['id' => 'llama3.1', 'name' => 'Llama 3.1 — good all-around local model (recommended)'],
                ['id' => 'llama3.2', 'name' => 'Llama 3.2 — lightweight, for modest hardware'],
                ['id' => 'mistral', 'name' => 'Mistral 7B — fast local option'],
                ['id' => 'qwen2.5', 'name' => 'Qwen 2.5 — strong multilingual local model'],
                ['id' => 'phi3', 'name' => 'Phi-3 — very lightweight'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | A single Default Provider (ai.provider) + Default Model is the baseline
    | for every AI task. Individual tasks may override it via
    | ai.tasks.{task}.provider / ai.tasks.{task}.model, and the lesson-plan
    | workflow additionally routes per generation mode (see
    | ai.lesson_plan_modes) with the balanced mode as failover baseline.
    |
    */

    'models' => [
        'default' => env('AI_MODEL_DEFAULT', 'gemini-3.6-flash'),
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
            'default_model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
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
        'deepseek' => [
            'name' => 'DeepSeek',
            'enabled' => env('AI_DEEPSEEK_ENABLED', false),
            'api_key' => env('DEEPSEEK_API_KEY', ''),
            'default_model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        ],
        'openrouter' => [
            'name' => 'OpenRouter (Fallback)',
            'enabled' => env('AI_OPENROUTER_ENABLED', false),
            'api_key' => env('OPENROUTER_API_KEY', ''),
            'default_model' => env('OPENROUTER_MODEL', 'nvidia/nemotron-3.5-lightning:free'),
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
    | Fallback Chain
    |--------------------------------------------------------------------------
    |
    | Ordered providers tried when the task's primary provider fails with a
    | transient error (timeout, provider outage, provider rate limit, network
    | failure). Providers that are not configured/available are skipped.
    | Application-level validation errors never trigger fallback.
    |
    */

    'fallback_chain' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('AI_FALLBACK_CHAIN', 'gemini,openrouter,openai,claude,deepseek'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Goal-Specific AI Tasks
    |--------------------------------------------------------------------------
    |
    | Each AI task defines its purpose, generation settings, RAG context,
    | token budgets, timeout and server-side rate limit. Tasks without an
    | entry here fall back to ai.stages / ai.generation defaults.
    |
    */

    'tasks' => [
        'lesson_plan_suggestion' => [
            'purpose' => 'Identify lesson plan weaknesses/gaps and suggest practical improvements aligned to applicable PPST/COT indicators.',
            'max_input_tokens' => (int) env('AI_TASK_LP_SUGGESTION_MAX_INPUT', 12000),
            'max_output_tokens' => 4096,
            'temperature' => 0.4,
            'timeout' => 90,
            'rate_limit' => ['limit' => 10, 'decay' => 60],
        ],
        'lesson_plan_summary' => [
            'purpose' => 'Summarize only the instructionally relevant content of a lesson plan.',
            'max_input_tokens' => (int) env('AI_TASK_LP_SUMMARY_MAX_INPUT', 12000),
            'max_output_tokens' => 4096,
            'temperature' => 0.3,
            'timeout' => 90,
            'rate_limit' => ['limit' => 15, 'decay' => 60],
        ],
        'cot_indicator_analysis' => [
            'purpose' => 'Compare observation evidence against a selected COT indicator; explain evidence and gaps. Never assigns final ratings.',
            'max_input_tokens' => 8000,
            'max_output_tokens' => 4096,
            'temperature' => 0.3,
            'timeout' => 90,
            'rate_limit' => ['limit' => 12, 'decay' => 60],
        ],
        'overall_recommendation' => [
            'purpose' => 'Synthesize observation evidence and ratings into prioritized actionable recommendations. Advisory only.',
            'max_input_tokens' => 8000,
            'max_output_tokens' => 2048,
            'temperature' => 0.5,
            'timeout' => 90,
            'rate_limit' => ['limit' => 5, 'decay' => 300],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lesson Plan Generation Modes (Goal-Driven Model Routing)
    |--------------------------------------------------------------------------
    |
    | The lesson-plan workflow routes each request to the generation mode
    | whose strengths best match the teacher's subject and pedagogical
    | goals (see App\AI\Routing\LessonPlanModelRouter):
    |
    | - reasoning   → Math/Science inquiry, problem solving, data reasoning.
    |                 Prefers reasoning-dense models with structured rubrics.
    | - expressive  → English/Filipino literature, creative writing, arts.
    |                 Prefers expressive language models.
    | - structured  → Remedial, foundational literacy/numeracy, highly
    |                 scaffolded or multi-step instruction.
    | - balanced    → Stable baseline for everything else AND the automatic
    |                 failover target when a specialized mode fails.
    |
    | Each mode resolves to [provider, model]. Empty values inherit the
    | task-level override (ai.tasks.*) and then the global default, so a
    | fresh install routes by prompt style while using one stable model
    | until administrators assign specialized models per mode.
    |
    */

    'lesson_plan_modes' => [
        'reasoning' => [
            'label' => 'Reasoning — Math & Science Inquiry',
            'description' => 'Step-by-step reasoning, inquiry scaffolds and structured rubrics for analytical subjects.',
            'provider' => env('AI_LP_MODE_REASONING_PROVIDER', ''),
            'model' => env('AI_LP_MODE_REASONING_MODEL', ''),
            'temperature' => 0.3,
        ],
        'expressive' => [
            'label' => 'Expressive — Language & Creative Writing',
            'description' => 'Rich, expressive language feedback for literature, writing and arts lessons.',
            'provider' => env('AI_LP_MODE_EXPRESSIVE_PROVIDER', ''),
            'model' => env('AI_LP_MODE_EXPRESSIVE_MODEL', ''),
            'temperature' => 0.6,
        ],
        'structured' => [
            'label' => 'Structured — Remedial & Foundational',
            'description' => 'Highly scaffolded, step-by-step guidance for remedial and foundational lessons.',
            'provider' => env('AI_LP_MODE_STRUCTURED_PROVIDER', ''),
            'model' => env('AI_LP_MODE_STRUCTURED_MODEL', ''),
            'temperature' => 0.3,
        ],
        'balanced' => [
            'label' => 'Balanced — General (Baseline)',
            'description' => 'Stable general-purpose baseline. Also the automatic failover target.',
            'provider' => env('AI_LP_MODE_BALANCED_PROVIDER', ''),
            'model' => env('AI_LP_MODE_BALANCED_MODEL', ''),
            'temperature' => 0.4,
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
        'max_input_tokens' => 16000,
        // Gemini 3.x thinking effort: 'low' keeps answers complete within the
        // token budgets (thinking tokens count toward maxOutputTokens).
        'thinking_level' => env('AI_THINKING_LEVEL', 'low'),
        'timeout' => 60,
    ],

    'stages' => [
        'pre_observation' => [
            'temperature' => 0.3,
            'max_output_tokens' => 2048,
        ],
        'observation_guidance' => [
            'temperature' => 0.4,
            'max_output_tokens' => 1536,
        ],
        'feedback' => [
            'temperature' => 0.4,
            'max_output_tokens' => 2048,
        ],
        'post_conference' => [
            'temperature' => 0.5,
            'max_output_tokens' => 1536,
        ],
        'final_report' => [
            'temperature' => 0.6,
            'max_output_tokens' => 3072,
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
            'observation_comparison' => ['limit' => 10, 'decay' => 60],
            'lesson_plan_suggestion' => ['limit' => 10, 'decay' => 60],
            'lesson_plan_summary' => ['limit' => 15, 'decay' => 60],
            'cot_indicator_analysis' => ['limit' => 12, 'decay' => 60],
            'overall_recommendation' => ['limit' => 5,  'decay' => 300],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Estimation
    |--------------------------------------------------------------------------
    |
    | Admin AI Usage & Cost Monitoring uses these prices to estimate spend per
    | AI call. Prices are stated in USD per 1,000,000 tokens (providers bill in
    | USD); the estimate is converted to the display currency via
    | usd_to_php_rate. Cost is computed on the fly — historical logs are never
    | mutated — so adjusting these values changes what the admin page reports.
    |
    | Resolution order per call:
    |   1. ai.pricing.models.{provider}.{model}  (exact model match)
    |   2. ai.pricing.providers.{provider}       (provider fallback)
    |   3. unpriced -> 0.00
    |
    | Free/local models (OpenRouter `:free` ids, Ollama) price at 0.00.
    |
    */

    'pricing' => [
        'currency' => 'PHP',
        'currency_symbol' => '₱',
        'usd_to_php_rate' => (float) env('AI_USD_TO_PHP_RATE', 58.0),

        // Per-provider fallback: USD per 1M tokens.
        'providers' => [
            'gemini' => ['input' => 0.50, 'output' => 1.50],
            'openai' => ['input' => 2.50, 'output' => 10.00],
            'claude' => ['input' => 3.00, 'output' => 15.00],
            'deepseek' => ['input' => 0.27, 'output' => 1.10],
            'openrouter' => ['input' => 0.15, 'output' => 0.60],
            'ollama' => ['input' => 0.00, 'output' => 0.00],
            'python' => ['input' => 0.00, 'output' => 0.00],
        ],

        // Finer per-model overrides (USD per 1M tokens) for the curated
        // model_catalog ids. Placeholder estimates — tune to current pricing.
        'models' => [
            'gemini' => [
                'gemini-3.6-flash' => ['input' => 0.10, 'output' => 0.40],
                'gemini-3.5-flash' => ['input' => 0.075, 'output' => 0.30],
                'gemini-3.5-flash-lite' => ['input' => 0.05, 'output' => 0.15],
                'gemini-flash-latest' => ['input' => 0.075, 'output' => 0.30],
                'gemini-3.1-pro-preview' => ['input' => 2.50, 'output' => 15.00],
                'gemini-2.5-flash' => ['input' => 0.30, 'output' => 2.50],
                'gemini-2.5-pro' => ['input' => 1.25, 'output' => 10.00],
                'gemini-pro-latest' => ['input' => 1.25, 'output' => 10.00],
            ],
            'openai' => [
                'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
                'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
                'gpt-4.1' => ['input' => 2.00, 'output' => 8.00],
                'gpt-4.1-mini' => ['input' => 0.40, 'output' => 1.60],
            ],
            'claude' => [
                'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
                'claude-3-7-sonnet-latest' => ['input' => 3.00, 'output' => 15.00],
                'claude-3-5-haiku-latest' => ['input' => 0.80, 'output' => 4.00],
            ],
            'deepseek' => [
                'deepseek-chat' => ['input' => 0.27, 'output' => 1.10],
                'deepseek-reasoner' => ['input' => 0.55, 'output' => 2.19],
            ],
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
        'python_path' => env('AI_PYTHON_PATH', 'python'),
        'timeout' => env('AI_PYTHON_BRIDGE_TIMEOUT', 60),
        'provider' => env('AI_PYTHON_BRIDGE_PROVIDER', ''),
        'default_model' => env('AI_PYTHON_BRIDGE_DEFAULT_MODEL', 'gemini-3.6-flash'),
    ],

];
