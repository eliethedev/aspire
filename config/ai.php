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
    | Select the active AI provider and configure model selection per
    | observation stage. Models can vary by task complexity.
    |
    */

    'provider' => env('AI_PROVIDER', 'gemini'),

    'models' => [
        'default' => env('AI_MODEL_DEFAULT', 'gemini-2.0-flash'),
        'pre_observation' => env('AI_MODEL_PRE_OBSERVATION', 'gemini-2.0-flash'),
        'observation_guidance' => env('AI_MODEL_OBSERVATION_GUIDANCE', 'gemini-2.0-flash'),
        'feedback' => env('AI_MODEL_FEEDBACK', 'gemini-2.0-flash'),
        'post_conference' => env('AI_MODEL_POST_CONFERENCE', 'gemini-2.0-flash'),
        'final_report' => env('AI_MODEL_FINAL_REPORT', 'gemini-2.0-flash'),
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
        'per_hour'   => (int) env('AI_RATE_LIMIT_PER_HOUR', 200),
        'operations' => [
            'pre_observation'    => ['limit' => 15, 'decay' => 60],
            'post_observation'   => ['limit' => 10, 'decay' => 60],
            'post_conference'    => ['limit' => 10, 'decay' => 60],
            'final_report'       => ['limit' => 5,  'decay' => 300],
            'observation_guidance' => ['limit' => 20, 'decay' => 60],
            'feedback'           => ['limit' => 20, 'decay' => 60],
        ],
    ],

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

];
