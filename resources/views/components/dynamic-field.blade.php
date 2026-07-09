@php
    $fieldKey = $field->key;
    $rawValue = old($fieldKey, $value);
    $fieldValue = is_array($rawValue) ? json_encode($rawValue) : $rawValue;
    $isRequired = $field->required;
    $labelClass = 'block text-sm font-medium text-gray-700 mb-1';
    $requiredMark = $isRequired ? ' <span class="text-red-500">*</span>' : '';
    $hasError = isset($errors[$fieldKey]);
    $errorMsg = $hasError ? (is_array($errors[$fieldKey]) ? implode(', ', $errors[$fieldKey]) : $errors[$fieldKey]) : null;
    $inputClass = 'w-full px-3 py-2 rounded-lg border ' . ($hasError ? 'border-red-400' : 'border-gray-300') . ' text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm';
    $errorClass = 'mt-1 text-sm text-red-400';
@endphp

@switch($field->type)
    @case('heading')
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $field->label }}</h3>
        @break

    @case('paragraph')
        <p class="text-sm text-gray-500 mb-2">{{ $field->label }}</p>
        @break

    @case('hr')
        <hr class="my-4 border-gray-200">
        @break

    @case('textarea')
        <div>
            <label for="{{ $fieldKey }}" class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <textarea name="{{ $fieldKey }}" id="{{ $fieldKey }}" rows="4"
                      placeholder="{{ $field->placeholder }}"
                      class="{{ $inputClass }}">{{ $fieldValue }}</textarea>
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
        @break

    @case('select')
        <div>
            <label for="{{ $fieldKey }}" class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <select name="{{ $fieldKey }}" id="{{ $fieldKey }}"
                    class="w-full px-3 py-2 rounded-lg border {{ $hasError ? 'border-red-400' : 'border-gray-300' }} text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                <option value="">Select {{ $field->label }}...</option>
                @foreach($getOptions() as $opt)
                    @php
                        $optValue = is_array($opt) ? $opt['value'] : $opt;
                        $optLabel = is_array($opt) ? $opt['label'] : $opt;
                    @endphp
                    <option value="{{ $optValue }}" {{ $fieldValue == $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                @endforeach
            </select>
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
        @break

    @case('radio')
        <div>
            <label class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <div class="space-y-2">
                @foreach($getOptions() as $opt)
                    @php
                        $optValue = is_array($opt) ? $opt['value'] : $opt;
                        $optLabel = is_array($opt) ? $opt['label'] : $opt;
                    @endphp
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="{{ $fieldKey }}" value="{{ $optValue }}"
                               {{ $isChecked($optValue) ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">{{ $optLabel }}</span>
                    </label>
                @endforeach
            </div>
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
        @break

    @case('checkbox')
        <div>
            <label class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <div class="space-y-2">
                @foreach($getOptions() as $opt)
                    @php
                        $optValue = is_array($opt) ? $opt['value'] : $opt;
                        $optLabel = is_array($opt) ? $opt['label'] : $opt;
                    @endphp
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="{{ $fieldKey }}[]" value="{{ $optValue }}"
                               {{ $isChecked($optValue) ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500 rounded">
                        <span class="text-sm text-gray-700">{{ $optLabel }}</span>
                    </label>
                @endforeach
            </div>
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
        @break

    @case('file')
        <div>
            <label for="{{ $fieldKey }}" class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <input type="file" name="{{ $fieldKey }}" id="{{ $fieldKey }}"
                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
        @break

    @case('date')
        <div>
            <label for="{{ $fieldKey }}" class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <input type="date" name="{{ $fieldKey }}" id="{{ $fieldKey }}"
                   value="{{ $fieldValue }}"
                   class="{{ $inputClass }}">
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
        @break

    @case('time')
        <div>
            <label for="{{ $fieldKey }}" class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <input type="time" name="{{ $fieldKey }}" id="{{ $fieldKey }}"
                   value="{{ $fieldValue }}"
                   class="{{ $inputClass }}">
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
        @break

    @case('number')
        <div>
            <label for="{{ $fieldKey }}" class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <input type="number" name="{{ $fieldKey }}" id="{{ $fieldKey }}"
                   value="{{ $fieldValue }}"
                   placeholder="{{ $field->placeholder }}"
                   class="{{ $inputClass }}">
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
        @break

    @default
        <div>
            <label for="{{ $fieldKey }}" class="{{ $labelClass }}">{!! $field->label !!}{!! $requiredMark !!}</label>
            @if($field->help_text)
                <p class="text-xs text-gray-500 mb-2">{{ $field->help_text }}</p>
            @endif
            <input type="text" name="{{ $fieldKey }}" id="{{ $fieldKey }}"
                   value="{{ $fieldValue }}"
                   placeholder="{{ $field->placeholder }}"
                   class="{{ $inputClass }}">
            @if($errorMsg)<p class="{{ $errorClass }}">{{ $errorMsg }}</p>@endif
        </div>
@endswitch
