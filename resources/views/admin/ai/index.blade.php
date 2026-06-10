@extends('layouts.admin')

@section('title', 'AI Settings')

@section('content')
<div class="max-w-7xl mx-auto px-6 space-y-8">
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-dark">AI Settings</h1>
                <p class="text-dark mt-1">Manage AI-powered features, provider configuration, and view usage statistics.</p>
            </div>
            <form method="POST" action="{{ route('admin.ai.test') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Test AI Connectivity
                </button>
            </form>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 {{ $config['enabled'] ? 'bg-emerald-100' : 'bg-red-100' }} rounded-lg">
                    <svg class="w-6 h-6 {{ $config['enabled'] ? 'text-emerald-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-dark">AI Processing</p>
                    <p class="text-2xl font-bold {{ $config['enabled'] ? 'text-emerald-600' : 'text-red-600' }}">{{ $config['enabled'] ? 'Enabled' : 'Disabled' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 {{ env('GOOGLE_GEMINI_API_KEY') ? 'bg-emerald-100' : 'bg-red-100' }} rounded-lg">
                    <svg class="w-6 h-6 {{ env('GOOGLE_GEMINI_API_KEY') ? 'text-emerald-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-dark">API Key</p>
                    <p class="text-2xl font-bold {{ env('GOOGLE_GEMINI_API_KEY') ? 'text-emerald-600' : 'text-red-600' }}">{{ env('GOOGLE_GEMINI_API_KEY') ? 'Configured' : 'Missing' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-dark">Provider</p>
                    <p class="text-2xl font-bold text-dark">{{ ucfirst($config['provider']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 {{ $config['fallback'] ? 'bg-amber-100' : 'bg-gray-100' }} rounded-lg">
                    <svg class="w-6 h-6 {{ $config['fallback'] ? 'text-amber-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-dark">Fallback</p>
                    <p class="text-2xl font-bold {{ $config['fallback'] ? 'text-amber-600' : 'text-gray-600' }}">{{ $config['fallback'] ? 'Enabled' : 'Disabled' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Form -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-8">
        <h2 class="text-xl font-semibold mb-6 text-dark">Configuration</h2>
        <form method="POST" action="{{ route('admin.ai.update') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">AI Enabled</label>
                    <select name="ai_enabled" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="1" {{ $config['enabled'] ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !$config['enabled'] ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Fallback Enabled</label>
                    <select name="ai_fallback_enabled" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="1" {{ $config['fallback'] ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !$config['fallback'] ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Logging Enabled</label>
                    <select name="ai_logging_enabled" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="1" {{ ($config['logging']['enabled'] ?? true) ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !($config['logging']['enabled'] ?? true) ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Provider</label>
                    <select name="ai_provider" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="gemini" {{ $config['provider'] === 'gemini' ? 'selected' : '' }}>Google Gemini</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark mb-2">API Key</label>
                    <input type="password" name="ai_api_key" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Leave empty to keep current" value="">
                </div>
            </div>

            <h3 class="text-lg font-semibold text-dark pt-4 border-t border-gray-200">Model Selection</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Default Model</label>
                    <input type="text" name="ai_model_default" value="{{ $config['models']['default'] }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Pre-Observation Model</label>
                    <input type="text" name="ai_model_pre_observation" value="{{ $config['models']['pre_observation'] }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Observation Guidance Model</label>
                    <input type="text" name="ai_model_observation_guidance" value="{{ $config['models']['observation_guidance'] }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Feedback Model</label>
                    <input type="text" name="ai_model_feedback" value="{{ $config['models']['feedback'] }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Post-Conference Model</label>
                    <input type="text" name="ai_model_post_conference" value="{{ $config['models']['post_conference'] }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Final Report Model</label>
                    <input type="text" name="ai_model_final_report" value="{{ $config['models']['final_report'] }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>

    <!-- Usage Statistics -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-8">
        <h2 class="text-xl font-semibold mb-6 text-dark">Usage Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <p class="text-sm text-dark">Total Calls</p>
                <p class="text-3xl font-bold text-dark">{{ $usageStats['total_calls'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <p class="text-sm text-dark">Success Rate</p>
                <p class="text-3xl font-bold text-emerald-600">{{ $usageStats['success_rate'] }}%</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <p class="text-sm text-dark">Avg Response Time</p>
                <p class="text-3xl font-bold text-dark">{{ $usageStats['average_response_time'] }}ms</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <p class="text-sm text-dark">Total Tokens</p>
                <p class="text-3xl font-bold text-dark">{{ number_format($usageStats['total_tokens']) }}</p>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-dark mb-4">Calls by Stage</h3>
        <div class="overflow-hidden border border-gray-200 rounded-xl">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Successful</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Failed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($usageStats['calls_by_stage'] as $stage)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $stage->stage) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $stage->total }}</td>
                            <td class="px-6 py-4 text-sm text-emerald-600">{{ $stage->successful }}</td>
                            <td class="px-6 py-4 text-sm text-red-600">{{ $stage->total - $stage->successful }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">No AI usage data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usageStats['recent_calls']->isNotEmpty())
        <h3 class="text-lg font-semibold text-dark mb-4 mt-8">Recent Calls</h3>
        <div class="overflow-hidden border border-gray-200 rounded-xl">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Response Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($usageStats['recent_calls'] as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $log->stage) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $log->model ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @if($log->success)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Success</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800" title="{{ $log->error_message }}">Failed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $log->response_time_ms ? $log->response_time_ms . 'ms' : 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
