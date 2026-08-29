<?php

namespace Tests\Unit\AI;

use App\AI\Bridge\PythonAIBridge;
use App\AI\Providers\PythonBridgeProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PythonBridgeProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeBridge(bool $enabled = true): PythonAIBridge
    {
        $bridge = Mockery::mock(PythonAIBridge::class);
        $bridge->shouldReceive('isEnabled')->andReturn($enabled);

        return $bridge;
    }

    public function test_is_available_delegates_to_bridge(): void
    {
        $bridge = $this->makeBridge(enabled: true);
        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');

        $this->assertTrue($provider->isAvailable());
    }

    public function test_is_unavailable_when_bridge_disabled(): void
    {
        $bridge = $this->makeBridge(enabled: false);
        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');

        $this->assertFalse($provider->isAvailable());
    }

    public function test_generate_returns_text_on_success(): void
    {
        $bridge = $this->makeBridge(enabled: true);
        $bridge->shouldReceive('generate')
            ->once()
            ->with('gemini', 'gemini-3.6-flash', 'Hello', Mockery::type('array'))
            ->andReturn([
                'success' => true,
                'text' => 'Hello from Python',
                'model' => 'gemini-3.6-flash',
                'provider' => 'gemini',
                'tokens' => 50,
            ]);

        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');
        $result = $provider->generate('Hello');

        $this->assertSame('Hello from Python', $result);
    }

    public function test_generate_returns_null_on_failure(): void
    {
        $bridge = $this->makeBridge(enabled: true);
        $bridge->shouldReceive('generate')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'API key invalid',
            ]);

        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');
        $result = $provider->generate('Hello');

        $this->assertNull($result);
    }

    public function test_generate_returns_null_when_bridge_returns_null(): void
    {
        $bridge = $this->makeBridge(enabled: true);
        $bridge->shouldReceive('generate')
            ->once()
            ->andReturn(null);

        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');
        $result = $provider->generate('Hello');

        $this->assertNull($result);
    }

    public function test_generate_json_returns_decoded_json(): void
    {
        $bridge = $this->makeBridge(enabled: true);
        $bridge->shouldReceive('generateJson')
            ->once()
            ->andReturn(['key' => 'value']);

        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gpt-4o');
        $result = $provider->generateJson('Return JSON');

        $this->assertSame(['key' => 'value'], $result);
    }

    public function test_generate_json_falls_back_to_text_recovery_when_bridge_returns_null(): void
    {
        $bridge = $this->makeBridge(enabled: true);
        $bridge->shouldReceive('generateJson')
            ->once()
            ->andReturn(null);
        $bridge->shouldReceive('generate')
            ->once()
            ->andReturn([
                'success' => true,
                'text' => '{"result": "ok"}',
                'tokens' => 30,
            ]);

        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gpt-4o');
        $result = $provider->generateJson('Return JSON');

        $this->assertSame(['result' => 'ok'], $result);
    }

    public function test_get_last_usageTracks_tokens(): void
    {
        $bridge = $this->makeBridge(enabled: true);
        $bridge->shouldReceive('generate')
            ->once()
            ->andReturn([
                'success' => true,
                'text' => 'done',
                'tokens' => 120,
            ]);

        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');
        $provider->generate('test');

        $usage = $provider->getLastUsage();
        $this->assertSame(0, $usage['input']);
        $this->assertSame(120, $usage['output']);
    }

    public function test_provider_name_is_python(): void
    {
        $bridge = $this->makeBridge();
        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');

        $this->assertSame('python', $provider->getProviderName());
    }

    public function test_set_model_updates_model_name(): void
    {
        $bridge = $this->makeBridge();
        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');
        $provider->setModel('gpt-4o');

        $this->assertSame('gpt-4o', $provider->getModelName());
    }

    public function test_generate_returns_null_when_bridge_disabled(): void
    {
        $bridge = $this->makeBridge(enabled: false);
        app()->instance(PythonAIBridge::class, $bridge);

        $provider = new PythonBridgeProvider(model: 'gemini-3.6-flash');
        $result = $provider->generate('Hello');

        $this->assertNull($result);
    }
}
