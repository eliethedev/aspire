@extends('layouts.admin')

@section('title', 'Create Announcement')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.announcements.index') }}"
               class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:text-gray-500 hover:border-gray-300 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create Announcement</h1>
                <p class="text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Compose a new announcement and choose how to deliver it.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Announcement Details</h2>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                           class="w-full px-4 py-2.5 rounded-lg border text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                           {{ $errors->has('title') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}"
                           placeholder="e.g., System Maintenance on Saturday"
                           maxlength="255">
                    <div class="flex items-center justify-between mt-1.5">
                        @error('title')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @else
                            <p class="text-xs text-gray-400 dark:text-gray-500">A clear, concise title for your announcement.</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" x-data="{ chars: {{ strlen(old('message', '')) }} }">
                        <textarea id="message" name="message" rows="8" required
                                  x-on:input="chars = $el.value.length"
                                  class="w-full px-4 py-3 rounded-lg border text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow resize-y
                                  {{ $errors->has('message') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}"
                                  placeholder="Write your announcement message here...">{{ old('message') }}</textarea>
                        <div class="absolute bottom-3 right-3 text-xs text-gray-400 dark:text-gray-500 bg-white px-1.5" x-text="chars + ' chars'"></div>
                    </div>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Link
                        <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 dark:text-gray-500 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </span>
                        <input type="url" id="link" name="link" value="{{ old('link') }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-lg border text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                               {{ $errors->has('link') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}"
                               placeholder="https://example.com/document">
                    </div>
                    @error('link')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Recipients will see a "View Details" button linking to this URL.</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delivery Settings</h2>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Delivery Method <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" x-data="{ selected: '{{ old('type', 'in_app') }}' }">
                        <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200"
                               :class="selected === 'in_app' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:bg-gray-800'">
                            <input type="radio" name="type" value="in_app" class="sr-only"
                                   x-on:change="selected = 'in_app'"
                                   {{ old('type', 'in_app') === 'in_app' ? 'checked' : '' }}>
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">In-App Only</span>
                                    <svg x-show="selected === 'in_app'" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">Send as in-app notification</p>
                            </div>
                        </label>

                        <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200"
                               :class="selected === 'email' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:bg-gray-800'">
                            <input type="radio" name="type" value="email" class="sr-only"
                                   x-on:change="selected = 'email'"
                                   {{ old('type') === 'email' ? 'checked' : '' }}>
                            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Email Only</span>
                                    <svg x-show="selected === 'email'" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">Send as email only</p>
                            </div>
                        </label>

                        <label class="relative flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all duration-200"
                               :class="selected === 'both' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:bg-gray-800'">
                            <input type="radio" name="type" value="both" class="sr-only"
                                   x-on:change="selected = 'both'"
                                   {{ old('type') === 'both' ? 'checked' : '' }}>
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Both</span>
                                    <svg x-show="selected === 'both'" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">Send as both notification and email</p>
                            </div>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Target Users</label>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">Select specific roles, or leave all unchecked to send to <strong>everyone</strong>.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" x-data="{ checked: [] }">
                        @php
                            $roles = [
                                'admin' => ['label' => 'Admins', 'color' => 'indigo', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                                'supervisor' => ['label' => 'Supervisors', 'color' => 'amber', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                                'teacher' => ['label' => 'Teachers', 'color' => 'emerald', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                                'school_head' => ['label' => 'School Heads', 'color' => 'rose', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                            ];
                        @endphp
                        @foreach($roles as $value => $meta)
                        <label class="relative flex items-center gap-3 p-3.5 rounded-lg border-2 cursor-pointer transition-all duration-200"
                               :class="checked.includes('{{ $value }}') ? 'border-{{ $meta['color'] }}-500 bg-{{ $meta['color'] }}-50/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:bg-gray-800'">
                            <input type="checkbox" name="target_roles[]" value="{{ $value }}"
                                   class="sr-only"
                                   x-on:change="if($el.checked) { if(!checked.includes('{{ $value }}')) checked.push('{{ $value }}') } else { checked = checked.filter(r => r !== '{{ $value }}') }"
                                   {{ in_array($value, old('target_roles', [])) ? 'checked' : '' }}>
                            <div class="w-9 h-9 rounded-lg bg-{{ $meta['color'] }}-100 text-{{ $meta['color'] }}-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $meta['label'] }}</span>
                            </div>
                            <svg x-show="checked.includes('{{ $value }}')" class="w-4 h-4 text-{{ $meta['color'] }}-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </label>
                        @endforeach
                    </div>
                    @error('target_roles')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 dark:text-gray-400 dark:text-gray-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Summary</h2>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-2 text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>In-app notifications are delivered immediately upon sending.</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Emails are sent using the configured SMTP server.</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Sent announcements cannot be edited or retracted.</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.announcements.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:text-gray-100 transition-colors">
                Cancel
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" name="send_now" value="0"
                        class="px-5 py-2.5 border border-gray-300 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 hover:border-gray-400 font-medium text-sm transition-all">
                    Save as Draft
                </button>
                <button type="submit" name="send_now" value="1"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium text-sm shadow-sm shadow-indigo-200 transition-all">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send Now
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
