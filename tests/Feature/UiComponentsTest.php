<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiComponentsTest extends TestCase
{
    public function test_stat_card_renders_label_value_and_icon(): void
    {
        $html = Blade::render(
            '<x-stat-card label="Total" :value="5" accent="amber"><x-slot name="icon"><svg><path d="M1"/></svg></x-slot></x-stat-card>'
        );

        $this->assertStringContainsString('Total', $html);
        $this->assertStringContainsString('>5<', $html);
        $this->assertStringContainsString('bg-amber-100', $html);
        $this->assertStringContainsString('<svg>', $html);
    }

    public function test_empty_state_renders_title_hint_and_action(): void
    {
        $html = Blade::render(
            '<x-empty-state title="No items" hint="Add one to begin." actionUrl="/items/create" actionLabel="Add Item" />'
        );

        $this->assertStringContainsString('No items', $html);
        $this->assertStringContainsString('Add one to begin.', $html);
        $this->assertStringContainsString('/items/create', $html);
        $this->assertStringContainsString('Add Item', $html);
    }

    public function test_section_card_renders_title_and_slot(): void
    {
        $html = Blade::render('<x-section-card title="Details" subtitle="Sub">Body content</x-section-card>');

        $this->assertStringContainsString('Details', $html);
        $this->assertStringContainsString('Sub', $html);
        $this->assertStringContainsString('Body content', $html);
    }
}
