<?php

namespace App\View\Components;

use App\Models\FormField;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DynamicField extends Component
{
    public function __construct(
        public FormField $field,
        public mixed $value = null,
        public array $errors = [],
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.dynamic-field');
    }

    public function inputClass(): string
    {
        $base = 'w-full px-3 py-2 rounded-lg border text-gray-900 focus:outline-none focus:ring-2 text-sm';

        if ($this->field->type === 'textarea') {
            $base = 'w-full px-3 py-2 rounded-lg border text-gray-900 focus:outline-none focus:ring-2 text-sm';
        }

        $hasError = isset($this->errors[$this->field->key]);
        $base .= $hasError ? ' border-red-400 focus:ring-red-500' : ' border-gray-300 focus:ring-blue-500';

        return $base;
    }

    public function errorClass(): string
    {
        return 'mt-1 text-sm text-red-400';
    }

    public function getOptions(): array
    {
        return $this->field->options ?? [];
    }

    public function isChecked(mixed $optionValue): bool
    {
        if ($this->field->type === 'checkbox') {
            $values = (array) $this->value;
            return in_array($optionValue, $values);
        }

        return (string) $this->value === (string) $optionValue;
    }
}
