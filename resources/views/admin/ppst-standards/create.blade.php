@extends('layouts.admin')

@section('title', 'Add PPST Standard')

@section('content')
<div class="max-w-6xl mx-auto px-4 space-y-6" x-data="ppstCreateForm()">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('admin.ppst-standards.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            PPST Standards
        </a>
        <svg class="w-3 h-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg>
        <span class="text-gray-900 dark:text-white font-medium">Add Standard</span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        <!-- Form -->
        <div class="flex-1 w-full bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200/60 dark:border-gray-800 overflow-hidden">
            <div class="px-6 lg:px-8 py-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-br from-indigo-50/60 to-violet-50/30 dark:from-indigo-900/10 dark:to-violet-900/10">
                <div class="flex gap-4">
                    <div class="hidden sm:flex w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white items-center justify-center shadow-sm shrink-0">
                        <i class="fas fa-plus text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Add PPST Standard</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">Create a new indicator in the PPST library. The <span class="font-semibold text-gray-700 dark:text-gray-300">strand is derived automatically</span> from the indicator code (e.g. <span class="font-mono bg-white dark:bg-gray-800 px-1.5 py-0.5 rounded border text-xs">1.1.2</span> → Strand <span class="font-mono bg-indigo-50 dark:bg-indigo-900/20 px-1 py-0.5 rounded text-indigo-600 dark:text-indigo-300 text-xs">1.1</span>).</p>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="mx-6 lg:mx-8 mt-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-4">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0"><i class="fas fa-triangle-exclamation text-sm"></i></div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Please fix the following</p>
                            <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.ppst-standards.store') }}" class="px-6 lg:px-8 py-6 space-y-7">
                @csrf

                <!-- Section: Identity -->
                <div>
                    <h2 class="text-xs font-bold tracking-widest uppercase text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400"><i class="fas fa-fingerprint text-[11px]"></i></span>
                        Indicator Identity
                    </h2>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-1">
                            <label for="indicator_code" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Indicator Code <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-hashtag text-xs"></i></span>
                                <input type="text" name="indicator_code" id="indicator_code" x-model="code" @input="updateStrand()" value="{{ old('indicator_code') }}"
                                       placeholder="e.g. 1.1.2"
                                       class="w-full pl-9 pr-24 py-2.5 rounded-xl border {{ $errors->has('indicator_code') ? 'border-red-300 focus:ring-red-500 focus:border-red-500 bg-red-50/30' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 focus:bg-white dark:focus:bg-gray-900' }} text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-mono transition-all" required>
                                <span class="absolute inset-y-0 right-1 flex items-center">
                                    <span x-show="strand" x-text="'Strand ' + strand" class="px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 text-xs font-mono font-bold"></span>
                                    <span x-show="!strand" class="px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 text-xs">—</span>
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Format <span class="font-mono bg-gray-50 dark:bg-gray-800 px-1 py-0.5 rounded border text-xs">D.S.I</span> — 1–2 digits per segment (e.g. 3.2.1).</p>
                            @error('indicator_code') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Sort Order</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-arrow-down-1-9 text-xs"></i></span>
                                <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $defaultSortOrder) }}"
                                       class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all">
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Lower numbers appear first. Default <span class="font-mono">{{ $defaultSortOrder }}</span>.</p>
                            @error('sort_order') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Content -->
                <div class="pt-6 border-t border-gray-100 dark:border-gray-800">
                    <h2 class="text-xs font-bold tracking-widest uppercase text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-800 flex items-center justify-center text-violet-600 dark:text-violet-400"><i class="fas fa-align-left text-[11px]"></i></span>
                        Content & Organization
                    </h2>

                    <div class="mt-4 space-y-5">
                        <div>
                            <label for="domain" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Domain <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-layer-group text-xs"></i></span>
                                <input type="text" name="domain" id="domain" list="domain-options" x-model="domain" value="{{ old('domain') }}"
                                       placeholder="e.g. Domain 1: Content Knowledge and Pedagogy"
                                       class="w-full pl-9 pr-3 py-2.5 rounded-xl border {{ $errors->has('domain') ? 'border-red-300 focus:ring-red-500 focus:border-red-500 bg-red-50/30' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 focus:bg-white dark:focus:bg-gray-900' }} text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all" required>
                            </div>
                            <datalist id="domain-options">
                                @foreach($domainOptions as $domain)
                                    <option value="{{ $domain }}"></option>
                                @endforeach
                            </datalist>
                            @if(count($domainOptions) > 0)
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 mr-1">Quick pick:</span>
                                    @foreach($domainOptions as $d)
                                        <button type="button" @click="domain = '{{ addslashes($d) }}'" class="px-2 py-1 rounded-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 dark:hover:text-indigo-300 hover:border-indigo-100 transition-colors">{{ Str::limit($d, 32) }}</button>
                                    @endforeach
                                </div>
                            @endif
                            @error('domain') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Description <span class="text-red-500">*</span></label>
                                <span class="text-xs text-gray-400 dark:text-gray-500" x-text="descLength + ' / 1000'"></span>
                            </div>
                            <textarea name="description" id="description" rows="4" maxlength="1000" x-model="description" @input="descLength = $el.value.length"
                                      placeholder="Full indicator description — what the teacher is expected to demonstrate (e.g. Apply knowledge of content within and across curriculum teaching areas)"
                                      class="w-full px-3.5 py-3 rounded-xl border {{ $errors->has('description') ? 'border-red-300 focus:ring-red-500 focus:border-red-500 bg-red-50/30' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 focus:bg-white dark:focus:bg-gray-900' }} text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm leading-relaxed transition-all" required>{{ old('description') }}</textarea>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Clear, observable language works best for COT raters.</p>
                            @error('description') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Status -->
                <div class="pt-6 border-t border-gray-100 dark:border-gray-800">
                    <h2 class="text-xs font-bold tracking-widest uppercase text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400"><i class="fas fa-toggle-on text-[11px]"></i></span>
                        Visibility
                    </h2>
                    <label class="mt-4 flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all" :class="isActive ? 'bg-emerald-50/50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800' : 'bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700'">
                        <input type="checkbox" name="is_active" value="1" x-model="isActive" {{ old('is_active', true) ? 'checked' : '' }} class="mt-0.5 w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold" :class="isActive ? 'text-emerald-900 dark:text-emerald-200' : 'text-gray-700 dark:text-gray-300'" x-text="isActive ? 'Active — visible for new COT Templates' : 'Inactive — hidden from new COT picks'"></p>
                            <p class="text-xs mt-1" :class="isActive ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'" x-text="isActive ? 'Historical observations keep their snapshot. Deactivate instead of deleting if the indicator is in use.' : 'Keeps the indicator in the library and in history, but removes it from new COT assembly.'"></p>
                        </div>
                        <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border" :class="isActive ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'" x-text="isActive ? 'Active' : 'Inactive'"></span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-sm hover:shadow font-semibold text-sm transition-all">
                        <i class="fas fa-check text-xs"></i> Add Standard
                    </button>
                    <a href="{{ route('admin.ppst-standards.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors">Cancel</a>
                    <span class="hidden sm:inline text-xs text-gray-400 dark:text-gray-500 ml-2"><span class="text-red-500">*</span> required</span>
                </div>
            </form>
        </div>

        <!-- Preview / Help -->
        <div class="w-full lg:w-[340px] shrink-0 space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/60 dark:border-gray-800 p-5">
                <h3 class="text-xs font-bold tracking-widest uppercase text-gray-500 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-eye text-indigo-500"></i> Live preview</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">How this indicator will appear in the library.</p>
                <div class="mt-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-gray-50/50 dark:from-gray-900 dark:to-gray-800/50 p-4">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 font-mono font-bold text-xs" x-text="code || '—'"></span>
                        <span class="px-2 py-1 rounded-lg border text-xs font-medium" :class="isActive ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border-emerald-100 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 border-gray-200 dark:border-gray-700'" x-text="isActive ? 'Active' : 'Inactive'"></span>
                        <span class="px-2 py-1 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs text-gray-500" x-text="strand ? 'Strand ' + strand : 'Strand —'"></span>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-gray-800 dark:text-gray-200" x-text="description || 'Indicator description will appear here…'"></p>
                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 truncate" x-text="domain || 'Domain will appear here'"></p>
                </div>
                <div class="mt-4 flex items-start gap-2 text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800 rounded-xl p-3">
                    <i class="fas fa-lightbulb mt-0.5"></i>
                    <span>Double-check the code: it must be unique. Strand updates automatically.</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-2xl p-5 border border-blue-100 dark:border-blue-900/30">
                <h3 class="text-sm font-bold text-blue-900 dark:text-blue-200 flex items-center gap-2"><span class="w-7 h-7 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs"><i class="fas fa-circle-info"></i></span> Quick guide</h3>
                <ul class="mt-3 text-sm text-blue-800 dark:text-blue-300 space-y-2">
                    <li class="flex gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span><span>Domain groups strands — keep naming consistent (<span class="font-mono bg-white/70 dark:bg-gray-900/30 px-1 py-0.5 rounded border text-xs">Domain X: Name</span>).</span></li>
                    <li class="flex gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span><span>Use observable language for COT raters.</span></li>
                    <li class="flex gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span><span>Sort order controls display order in the library.</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ppstCreateForm() {
    return {
        code: @json(old('indicator_code', '')),
        domain: @json(old('domain', '')),
        description: @json(old('description', '')),
        isActive: @json(old('is_active', true) ? true : false),
        strand: '',
        descLength: @json(mb_strlen(old('description', ''))),
        init() { this.updateStrand(); },
        updateStrand() {
            const parts = (this.code || '').trim().split('.');
            if (parts.length >= 2 && /^\d+$/.test(parts[0]) && /^\d+$/.test(parts[1])) {
                this.strand = parts[0] + '.' + parts[1];
            } else {
                this.strand = '';
            }
        }
    }
}
</script>
@endpush
