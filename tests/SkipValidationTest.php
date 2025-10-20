<?php

namespace LaravelProfaneTests;

use Illuminate\Contracts\Foundation\Application;
use LaravelProfane\ProfaneServiceProvider;
use Illuminate\Support\Facades\Config;
use Mockery;

class SkipValidationTest extends TestCase
{
    public function test_validation_is_skipped_when_config_enabled()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('bound')->with('profane.skip')->andReturn(false);
        $app->shouldReceive('runningUnitTests')->andReturn(true);

        Config::shouldReceive('get')
            ->once()
            ->with('laravel-profane', [])
            ->andReturn(['skip_on_tests' => true]);

        $provider = new ProfaneServiceProvider($app);

        $ref = new \ReflectionClass($provider);
        $method = $ref->getMethod('shouldSkipValidation');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($provider));
    }

    public function test_validation_is_not_skipped_when_config_disabled()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('bound')->with('profane.skip')->andReturn(false);
        $app->shouldReceive('runningUnitTests')->andReturn(true);

        Config::shouldReceive('get')
            ->once()
            ->with('laravel-profane', [])
            ->andReturn(['skip_on_tests' => false, 'always_skip' => false]);

        $provider = new ProfaneServiceProvider($app);

        $ref = new \ReflectionClass($provider);
        $method = $ref->getMethod('shouldSkipValidation');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($provider));
    }
}