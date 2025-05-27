<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Tests\TestHelpers;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class FeatureFlagServiceTest extends TestCase
{
    use TestHelpers;

    private FeatureFlagService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FeatureFlagService();
        Cache::flush();
    }

    public function test_returns_false_for_undefined_feature()
    {
        $this->assertFalse($this->service->isEnabled('non_existent_feature'));
    }

    public function test_returns_configured_feature_value()
    {
        Config::set('features.test_feature', true);
        
        $this->assertTrue($this->service->isEnabled('test_feature'));
        
        Config::set('features.test_feature', false);
        $this->service->clearCache('test_feature');
        
        $this->assertFalse($this->service->isEnabled('test_feature'));
    }

    public function test_enable_feature()
    {
        $this->service->enable('test_feature');
        
        $this->assertTrue($this->service->isEnabled('test_feature'));
    }

    public function test_disable_feature()
    {
        Config::set('features.test_feature', true);
        $this->service->clearCache('test_feature');
        
        $this->service->disable('test_feature');
        
        $this->assertFalse($this->service->isEnabled('test_feature'));
    }

    public function test_toggle_feature()
    {
        Config::set('features.test_feature', false);
        $this->service->clearCache('test_feature');
        
        $result = $this->service->toggle('test_feature');
        
        $this->assertTrue($result);
        $this->assertTrue($this->service->isEnabled('test_feature'));
        
        $result = $this->service->toggle('test_feature');
        
        $this->assertFalse($result);
        $this->assertFalse($this->service->isEnabled('test_feature'));
    }

    public function test_get_all_flags()
    {
        Config::set('features', [
            'feature_one' => true,
            'feature_two' => false,
            'feature_three' => true,
        ]);
        
        $flags = $this->service->getAllFlags();
        
        $this->assertEquals([
            'feature_one' => true,
            'feature_two' => false,
            'feature_three' => true,
        ], $flags);
    }

    public function test_when_feature_enabled()
    {
        Config::set('features.test_feature', true);
        $this->service->clearCache('test_feature');
        
        $executed = false;
        $result = $this->service->when('test_feature', function () use (&$executed) {
            $executed = true;
            return 'feature_enabled';
        });
        
        $this->assertTrue($executed);
        $this->assertEquals('feature_enabled', $result);
    }

    public function test_when_feature_disabled_with_fallback()
    {
        Config::set('features.test_feature', false);
        $this->service->clearCache('test_feature');
        
        $mainExecuted = false;
        $fallbackExecuted = false;
        
        $result = $this->service->when(
            'test_feature',
            function () use (&$mainExecuted) {
                $mainExecuted = true;
                return 'main_executed';
            },
            function () use (&$fallbackExecuted) {
                $fallbackExecuted = true;
                return 'fallback_executed';
            }
        );
        
        $this->assertFalse($mainExecuted);
        $this->assertTrue($fallbackExecuted);
        $this->assertEquals('fallback_executed', $result);
    }

    public function test_unless_feature_disabled()
    {
        Config::set('features.test_feature', false);
        $this->service->clearCache('test_feature');
        
        $executed = false;
        $result = $this->service->unless('test_feature', function () use (&$executed) {
            $executed = true;
            return 'feature_disabled';
        });
        
        $this->assertTrue($executed);
        $this->assertEquals('feature_disabled', $result);
    }

    public function test_unless_feature_enabled_with_fallback()
    {
        Config::set('features.test_feature', true);
        $this->service->clearCache('test_feature');
        
        $mainExecuted = false;
        $fallbackExecuted = false;
        
        $result = $this->service->unless(
            'test_feature',
            function () use (&$mainExecuted) {
                $mainExecuted = true;
                return 'main_executed';
            },
            function () use (&$fallbackExecuted) {
                $fallbackExecuted = true;
                return 'fallback_executed';
            }
        );
        
        $this->assertFalse($mainExecuted);
        $this->assertTrue($fallbackExecuted);
        $this->assertEquals('fallback_executed', $result);
    }

    public function test_caching_behavior()
    {
        Config::set('features.test_feature', true);
        
        $this->assertTrue($this->service->isEnabled('test_feature'));
        
        Config::set('features.test_feature', false);
        
        $this->assertTrue($this->service->isEnabled('test_feature'));
        
        $this->service->clearCache('test_feature');
        
        $this->assertFalse($this->service->isEnabled('test_feature'));
    }

    public function test_clear_all_cache()
    {
        Config::set('features', [
            'feature_one' => true,
            'feature_two' => true,
        ]);
        
        $this->assertTrue($this->service->isEnabled('feature_one'));
        $this->assertTrue($this->service->isEnabled('feature_two'));
        
        Config::set('features', [
            'feature_one' => false,
            'feature_two' => false,
        ]);
        
        $this->service->clearCache();
        
        $this->assertFalse($this->service->isEnabled('feature_one'));
        $this->assertFalse($this->service->isEnabled('feature_two'));
    }
}