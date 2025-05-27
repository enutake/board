<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class FeatureFlagService
{
    private const CACHE_TTL = 300;
    private const CACHE_PREFIX = 'feature_flag_';

    public function isEnabled(string $feature): bool
    {
        $cacheKey = self::CACHE_PREFIX . $feature;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($feature) {
            return $this->getFeatureFlagValue($feature);
        });
    }

    public function enable(string $feature): void
    {
        $this->setFeatureFlag($feature, true);
    }

    public function disable(string $feature): void
    {
        $this->setFeatureFlag($feature, false);
    }

    public function toggle(string $feature): bool
    {
        $newState = !$this->isEnabled($feature);
        $this->setFeatureFlag($feature, $newState);
        return $newState;
    }

    public function getAllFlags(): array
    {
        $configFlags = Config::get('features', []);
        $flags = [];
        
        foreach ($configFlags as $feature => $defaultValue) {
            $flags[$feature] = $this->isEnabled($feature);
        }
        
        return $flags;
    }

    public function clearCache(string $feature = null): void
    {
        if ($feature) {
            Cache::forget(self::CACHE_PREFIX . $feature);
        } else {
            $configFlags = Config::get('features', []);
            foreach (array_keys($configFlags) as $featureName) {
                Cache::forget(self::CACHE_PREFIX . $featureName);
            }
        }
    }

    private function getFeatureFlagValue(string $feature): bool
    {
        return (bool) Config::get("features.{$feature}", false);
    }

    private function setFeatureFlag(string $feature, bool $value): void
    {
        Config::set("features.{$feature}", $value);
        $this->clearCache($feature);
    }

    public function when(string $feature, callable $callback, callable $fallback = null)
    {
        if ($this->isEnabled($feature)) {
            return $callback();
        } elseif ($fallback) {
            return $fallback();
        }
        
        return null;
    }

    public function unless(string $feature, callable $callback, callable $fallback = null)
    {
        if (!$this->isEnabled($feature)) {
            return $callback();
        } elseif ($fallback) {
            return $fallback();
        }
        
        return null;
    }
}