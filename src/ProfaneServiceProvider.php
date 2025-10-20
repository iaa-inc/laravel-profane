<?php

namespace LaravelProfane;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use LaravelProfane\Console\PublishConfigCommand;
use Illuminate\Support\Facades\Config;

class ProfaneServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge package config so config() works without publishing
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-profane.php', 'laravel-profane');
    }

    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/lang', 'laravel-profane');

        $this->publishes([
            __DIR__.'/lang' => resource_path('lang/vendor/laravel-profane'),
        ]);

        // publish config when in console
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-profane.php' => config_path('laravel-profane.php'),
            ], 'config');

            $this->commands([
                PublishConfigCommand::class,
            ]);
        }

        // Rule for caseless content matching
        Validator::extend('profane', function ($attribute, $value, $parameters, $validator) {
            if ($this->shouldSkipValidation()) {
                return true;
            }

            return app(ProfaneValidator::class)->validate($attribute, $value, $parameters, $validator);
        }, Lang::get('laravel-profane::validation.profane'));

        Validator::replacer('profane', function ($message, $attribute) {
            return str_replace(':attribute', $attribute, $message);
        });

        // Rule for caseless but strict word matching
        Validator::extend('strictly_profane', function ($attribute, $value, $parameters, $validator) {
            if ($this->shouldSkipValidation()) {
                return true;
            }

            return app(ProfaneValidator::class)->validateStrict($attribute, $value, $parameters, $validator);
        }, Lang::get('laravel-profane::validation.profane'));

        Validator::replacer('strictly_profane', function ($message, $attribute) {
            return str_replace(':attribute', $attribute, $message);
        });
    }

    private function shouldSkipValidation(): bool
    {
        // explicit container override (can be set by app service provider)
        if ($this->app->bound('profane.skip')) {
            return (bool) $this->app->make('profane.skip');
        }

        // config-driven toggles
        $config = Config::get('laravel-profane', []);

        if (!empty($config['always_skip'])) {
            return true;
        }

        if (!empty($config['skip_on_tests']) && $this->app->runningUnitTests()) {
            return true;
        }

        return false;
    }
}
