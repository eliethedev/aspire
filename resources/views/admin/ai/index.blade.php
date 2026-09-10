@extends('layouts.admin')

@section('title', 'AI Settings')

@section('content')
@php
    $aiCatalog = $aiCatalog ?? config('ai.model_catalog', []);
@endphp
<div x-data="aiSettings({ provider: {{ Js::from($config['provider'] ?? 'gemini') }}, catalog: {{ Js::from($aiCatalog) }} })" class="max-w-7xl mx-auto px-6 space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Settings</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure AI providers and the single default model used for every feature, then view usage.</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Saving runs a live connectivity check on the selected provider/model first — a broken configuration is rejected before it reaches users.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.ai.restore') }}">
                @csrf
                <button type="submit" onclick="return confirm('Restore AI settings from the most recent backup?')" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                    Restore Last Save
                </button>
            </form>
            <form method="POST" action="{{ route('admin.ai.test') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                    Test Active Provider
                </button>
            </form>
            <form method="POST" action="{{ route('admin.ai.emergency') }}">
                @csrf
                <input type="hidden" name="action" value="{{ $config['enabled'] ? 'disable' : 'enable' }}">
                <button type="submit"
                    onclick="return {{ $config['enabled'] ? "confirm('Disable all AI processing now? Users will fall back to rule-based responses.')" : "confirm('Re-enable AI processing?')" }}"
                    class="px-4 py-2 rounded-lg transition-colors text-sm font-medium {{ $config['enabled']
                        ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/50'
                        : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                    {{ $config['enabled'] ? 'Disable AI Now' : 'Re-enable AI' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex gap-6 -mb-px overflow-x-auto">
            <button @click="activeTab = 'providers'" :class="activeTab === 'providers' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="py-3 px-1 border-b-2 text-sm font-medium transition-colors">
                Providers
            </button>
            <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="py-3 px-1 border-b-2 text-sm font-medium transition-colors">
                General
            </button>
            <button @click="activeTab = 'usage'" :class="activeTab === 'usage' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="py-3 px-1 border-b-2 text-sm font-medium transition-colors">
                Usage
            </button>
        </nav>
    </div>

    <form method="POST" action="{{ route('admin.ai.update') }}">
        @csrf

        <!-- PROVIDERS TAB -->
        <div x-show="activeTab === 'providers'" x-transition.opacity>
            <div class="space-y-4">
                <!-- Active Provider Selector -->
                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Default Provider &amp; Model</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Baseline for every AI feature — used for all goals, observation stages and recommendations. The lesson-plan workflow additionally auto-routes each request to a specialized generation mode (Reasoning, Expressive, Structured) matched to the lesson subject &amp; goals, falling back to this baseline on failure.</p>
                    <div class="flex flex-col md:flex-row md:items-end gap-4">
                        <div class="flex-1 md:max-w-xs">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Default Provider</label>
                            <select name="ai_provider" x-model="provider" @change="onProviderChanged()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="gemini" {{ $config['provider'] === 'gemini' ? 'selected' : '' }}>Google Gemini</option>
                                <option value="openai" {{ $config['provider'] === 'openai' ? 'selected' : '' }}>OpenAI</option>
                                <option value="claude" {{ $config['provider'] === 'claude' ? 'selected' : '' }}>Anthropic Claude</option>
                                <option value="openrouter" {{ $config['provider'] === 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                                <option value="deepseek" {{ $config['provider'] === 'deepseek' ? 'selected' : '' }}>DeepSeek</option>
                                <option value="ollama" {{ $config['provider'] === 'ollama' ? 'selected' : '' }}>Ollama (Local)</option>
                                @foreach($customProviders as $customProvider)
                                <option value="{{ $customProvider->slug }}" {{ $config['provider'] === $customProvider->slug ? 'selected' : '' }}>{{ $customProvider->name }} (Custom)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 md:max-w-md">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Default Model</label>
                            @include('admin.ai.partials.model-select', [
                                'name' => 'ai_model_default',
                                'id' => 'default-model-select',
                                'current' => $config['models']['default'] ?? '',
                                'provider' => $config['provider'] ?? 'gemini',
                                'catalog' => $aiCatalog,
                            ])
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Shows models for the provider selected above. Pick OpenRouter to see MiniMax.</p>
                        </div>
                    </div>
                </div>

                <!-- Provider Cards -->
                @foreach(['gemini', 'openai', 'claude', 'openrouter', 'ollama'] as $providerKey)
                @php
                    $providerCfg = $config['providers'][$providerKey] ?? [];
                    $providerStatus = $providerStatus[$providerKey] ?? ['enabled' => false, 'configured' => false];
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $providerStatus['configured'] ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-gray-100 dark:bg-gray-800' }}">
                                @if($providerKey === 'gemini')
                                    <svg class="w-5 h-5 {{ $providerStatus['configured'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                                @elseif($providerKey === 'openai')
                                    <svg class="w-5 h-5 {{ $providerStatus['configured'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/></svg>
                                @elseif($providerKey === 'claude')
                                    <svg class="w-5 h-5 {{ $providerStatus['configured'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M4.709 15.955l4.72-2.756.08-.046 2.803-1.636a.206.206 0 0 0 0-.357l-2.959-1.726-4.716-2.752a.206.206 0 0 0-.309.178v8.917a.206.206 0 0 0 .309.178h.072zm7.582-4.626l2.819 1.645 4.716 2.753a.206.206 0 0 0 .309-.179V5.642a.206.206 0 0 0-.309-.178l-4.72 2.756-2.815 1.643a.206.206 0 0 0 0 .357v.012z"/></svg>
                                @elseif($providerKey === 'openrouter')
                                    <svg class="w-5 h-5 {{ $providerStatus['configured'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                @else
                                    <svg class="w-5 h-5 {{ $providerStatus['configured'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $providerCfg['name'] ?? ucfirst($providerKey) }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $providerStatus['configured'] ? 'Connected' : 'Not configured' }} &middot; {{ $providerCfg['default_model'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $providerStatus['enabled'] ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                                {{ $providerStatus['enabled'] ? 'Enabled' : 'Disabled' }}
                            </span>
                            <button type="button" onclick="testProvider('{{ $providerKey }}')" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Test</button>
                        </div>
                    </div>

                    <!-- Provider Config Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Enabled</label>
                            <select name="ai_{{ $providerKey }}_enabled" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="1" {{ ($providerCfg['enabled'] ?? false) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !($providerCfg['enabled'] ?? false) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        @if($providerKey !== 'ollama')
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                            @if(!empty($maskedKeys[$providerKey]))
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 relative">
                                        <input type="text" value="{{ $maskedKeys[$providerKey] }}" readonly class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm font-mono cursor-default">
                                        <button type="button" onclick="copyApiKey('{{ $providerKey }}', '{{ addslashes(config('services.' . $providerKey . '.api_key', '')) }}')" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors" title="Copy API key">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Key is set. Leave empty below to keep it, or enter a new one to replace.</p>
                            @endif
                            <input type="password" name="ai_{{ $providerKey }}_api_key" placeholder="{{ !empty($maskedKeys[$providerKey]) ? 'Enter new key to replace' : 'Enter API key' }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 mt-{{ !empty($maskedKeys[$providerKey]) ? '2' : '0' }}">
                        </div>
                        @else
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Base URL</label>
                            <input type="text" name="ai_ollama_url" value="{{ $providerCfg['url'] ?? 'http://localhost:11434' }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        @endif

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Default Model</label>
                            @include('admin.ai.partials.model-select', [
                                'name' => 'ai_' . $providerKey . '_model',
                                'id' => $providerKey . '-model-select',
                                'current' => $providerCfg['default_model'] ?? '',
                                'provider' => $providerKey,
                                'catalog' => $aiCatalog,
                            ])
                            @if($providerKey === 'ollama')
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Only models pulled locally (e.g. <code>ollama pull llama3.1</code>) will work.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- GENERAL TAB -->
        <div x-show="activeTab === 'general'" x-transition.opacity>
            <div class="space-y-4">
                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Global Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">AI Processing</label>
                            <select name="ai_enabled" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="1" {{ $config['enabled'] ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ !$config['enabled'] ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Rule-Based Fallback</label>
                            <select name="ai_fallback_enabled" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="1" {{ $config['fallback'] ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ !$config['fallback'] ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Usage Logging</label>
                            <select name="ai_logging_enabled" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="1" {{ ($config['logging']['enabled'] ?? true) ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ !($config['logging']['enabled'] ?? true) ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Python Bridge -->
                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Python AI Bridge</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Advanced multi-model orchestration via Python backend.</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                            Optional
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Enable Python Bridge</label>
                        <select name="ai_python_bridge_enabled" class="w-full md:w-48 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="1" {{ ($config['python_bridge']['enabled'] ?? false) ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ !($config['python_bridge']['enabled'] ?? false) ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- USAGE TAB -->
        <div x-show="activeTab === 'usage'" x-transition.opacity>
            <div class="space-y-4">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Calls</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($usageStats['total_calls']) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Success Rate</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $usageStats['success_rate'] }}%</p>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Avg Response</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $usageStats['average_response_time'] }}ms</p>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Tokens</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($usageStats['total_tokens']) }}</p>
                    </div>
                </div>

                <!-- Calls by Stage -->
                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Calls by Stage</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Stage</th>
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Total</th>
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Successful</th>
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Failed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($usageStats['calls_by_stage'] as $stage)
                                    <tr>
                                        <td class="py-3 text-sm font-medium text-gray-900 dark:text-gray-100 capitalize">{{ str_replace('_', ' ', $stage->stage) }}</td>
                                        <td class="py-3 text-sm text-gray-600 dark:text-gray-300">{{ $stage->total }}</td>
                                        <td class="py-3 text-sm text-emerald-600 dark:text-emerald-400">{{ $stage->successful }}</td>
                                        <td class="py-3 text-sm text-red-600 dark:text-red-400">{{ $stage->total - $stage->successful }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">No usage data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Calls -->
                @if($usageStats['recent_calls']->isNotEmpty())
                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Recent Calls</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Stage</th>
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Model</th>
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Status</th>
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Response</th>
                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">When</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($usageStats['recent_calls'] as $log)
                                    <tr>
                                        <td class="py-3 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ str_replace('_', ' ', $log->stage) }}</td>
                                        <td class="py-3 text-sm text-gray-600 dark:text-gray-300">{{ $log->model ?? 'N/A' }}</td>
                                        <td class="py-3">
                                            @if($log->success)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Success</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300" title="{{ $log->error_message }}">Failed</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-sm text-gray-600 dark:text-gray-300">{{ $log->response_time_ms ? $log->response_time_ms . 'ms' : 'N/A' }}</td>
                                        <td class="py-3 text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" name="ai_skip_verify" value="1" class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:bg-gray-800 focus:ring-indigo-500">
                Save even if the connectivity check fails
            </label>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                Save Configuration
            </button>
        </div>
    </form>

    <!-- CUSTOM PROVIDERS -->
    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Custom Providers</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Add your own OpenAI-compatible provider (Groq, Together AI, Fireworks, LM Studio, vLLM, …). It becomes usable anywhere the built-in providers are, including the Default Provider dropdown above.</p>
            </div>
            <details class="group">
                <summary class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium cursor-pointer select-none">Add Provider</summary>
                <div class="mt-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <form method="POST" action="{{ route('admin.ai.providers.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Provider Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Groq AI" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Base URL</label>
                                <input type="url" name="base_url" value="{{ old('base_url') }}" required placeholder="e.g. https://api.groq.com/openai/v1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                                <input type="password" name="api_key" placeholder="Provider API key" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Default Model</label>
                                <input type="text" name="default_model" value="{{ old('default_model') }}" placeholder="e.g. llama-3.3-70b-versatile (blank = first model below)" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Models <span class="text-gray-400">(one per line: <code>model-id</code> or <code>model-id|Friendly Name</code>)</span></label>
                                <textarea name="models" rows="4" required placeholder="llama-3.3-70b-versatile|Llama 3.3 70B (versatile)&#10;llama-3.1-8b-instant|Llama 3.1 8B (instant)" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500">{{ old('models') }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Enabled</label>
                                <select name="enabled" class="w-full md:w-48 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option value="1" {{ old('enabled', '1') === '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('enabled', '1') === '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">Add Provider</button>
                        </div>
                    </form>
                </div>
            </details>
        </div>

        @forelse($customProviders as $customProvider)
        @php
            $customStatus = $providerStatus[$customProvider->slug] ?? ['configured' => (bool) $customProvider->api_key, 'enabled' => (bool) $customProvider->enabled];
            $modelsText = collect($customProvider->modelsList())
                ->map(fn ($m) => ($m['id'] ?? '').(isset($m['name']) && $m['name'] !== ($m['id'] ?? '') ? '|'.$m['name'] : ''))
                ->implode(PHP_EOL);
        @endphp
        <details class="border-t border-gray-200 dark:border-gray-800 py-4">
            <summary class="flex flex-wrap items-center justify-between gap-3 cursor-pointer select-none">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ ($customStatus['configured'] ?? false) ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-gray-100 dark:bg-gray-800' }}">
                        <svg class="w-5 h-5 {{ ($customStatus['configured'] ?? false) ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $customProvider->name }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ ($customStatus['configured'] ?? false) ? 'Connected' : 'Not configured' }} &middot; <span class="text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $customProvider->base_url }}</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($customStatus['enabled'] ?? false) ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                        {{ ($customStatus['enabled'] ?? false) ? 'Enabled' : 'Disabled' }}
                    </span>
                    <form method="POST" action="{{ route('admin.ai.providers.test', $customProvider) }}">
                        @csrf
                        <button type="submit" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Test</button>
                    </form>
                </div>
            </summary>
            <div class="mt-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <form method="POST" action="{{ route('admin.ai.providers.update', $customProvider) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Provider Name</label>
                            <input type="text" name="name" value="{{ $customProvider->name }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Base URL</label>
                            <input type="url" name="base_url" value="{{ $customProvider->base_url }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                            <input type="password" name="api_key" placeholder="{{ $customProvider->masked_api_key ? 'Leave blank to keep ('.substr($customProvider->masked_api_key, -4).')' : 'Enter API key' }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                            @if($customProvider->masked_api_key)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Current key: <span class="font-mono">{{ $customProvider->masked_api_key }}</span></p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Default Model</label>
                            <input type="text" name="default_model" value="{{ $customProvider->default_model }}" placeholder="Blank = first model below" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Models <span class="text-gray-400">(one per line: <code>model-id</code> or <code>model-id|Friendly Name</code>)</span></label>
                            <textarea name="models" rows="4" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500">{{ $modelsText }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Enabled</label>
                            <select name="enabled" class="w-full md:w-48 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="1" {{ $customProvider->enabled ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ ! $customProvider->enabled ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">Save Provider</button>
                        <button type="submit" form="delete-{{ $customProvider->id }}" onclick="return confirm('Delete "{{ $customProvider->name }}"? This provider will no longer be available.')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">Delete</button>
                    </div>
                </form>
                <form id="delete-{{ $customProvider->id }}" method="POST" action="{{ route('admin.ai.providers.destroy', $customProvider) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </details>
        @empty
        <p class="text-sm text-gray-400 dark:text-gray-500 py-4">No custom providers yet. Click "Add Provider" above to connect an OpenAI-compatible endpoint.</p>
        @endforelse
    </div>

    <!-- Provider connectivity test form (kept outside the main form to avoid illegal nesting) -->
    <form id="provider-test-form" method="POST" action="{{ route('admin.ai.test-provider') }}" class="hidden">
        @csrf
        <input type="hidden" name="provider" id="provider-test-input" value="">
    </form>
</div>

<script>
function aiSettings(init) {
    init = init || {};
    return {
        activeTab: 'providers',
        provider: init.provider || 'gemini',
        catalog: init.catalog || {},
        onProviderChanged() {
            const el = document.getElementById('default-model-select');
            if (!el) return;

            const wrap = el.closest('[data-model-field]');
            const customInput = wrap ? wrap.querySelector('[data-custom-input]') : null;
            const group = this.catalog[this.provider] || { models: [] };

            // The previous selection: either a real model id or a free-text
            // custom value carried in the hidden input.
            const previous = el.value;
            const wasCustom = previous === '__custom__';
            const previousModel = wasCustom ? (customInput ? customInput.value : '') : previous;

            el.innerHTML = '';
            group.models.forEach(function (m) {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.name;
                el.appendChild(opt);
            });
            const customOpt = document.createElement('option');
            customOpt.value = '__custom__';
            customOpt.textContent = 'Custom model\u2026';
            el.appendChild(customOpt);

            // Keep the current model when it is still offered by the new
            // provider; otherwise preserve it as a custom value so nothing is
            // silently dropped while the admin switches providers.
            if (previousModel && group.models.some(function (m) { return m.id === previousModel; })) {
                el.value = previousModel;
            } else {
                el.value = '__custom__';
                if (customInput) customInput.value = previousModel;
            }
            aiModelSelectChanged(el);
        },
    };
}

function testProvider(provider) {
    document.getElementById('provider-test-input').value = provider;
    document.getElementById('provider-test-form').submit();
}

function aiModelSelectChanged(selectEl) {
    const wrap = selectEl.closest('[data-model-field]');
    if (!wrap) return;
    const customInput = wrap.querySelector('[data-custom-input]');
    if (!customInput) return;

    const isCustom = selectEl.value === '__custom__';
    customInput.classList.toggle('hidden', !isCustom);
    if (isCustom) {
        customInput.focus();
    } else {
        customInput.value = '';
    }
}

function copyApiKey(provider, key) {
    if (!key) {
        alert('No API key found for ' + provider);
        return;
    }
    navigator.clipboard.writeText(key).then(function() {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 px-4 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg z-50 transition-opacity';
        toast.textContent = 'API key copied to clipboard';
        document.body.appendChild(toast);
        setTimeout(function() {
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }, 2000);
    });
}
</script>
@endsection
