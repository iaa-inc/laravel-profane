<?php
namespace LaravelProfane\Console;

use Illuminate\Console\Command;

class PublishConfigCommand extends Command
{
    protected $signature = 'profane:publish-config';
    protected $description = 'Publish the laravel-profane configuration file';

    public function handle()
    {
        // Forward to the standard vendor:publish with provider and tag
        $this->call('vendor:publish', [
            '--provider' => 'LaravelProfane\ProfaneServiceProvider',
            '--tag' => 'config',
        ]);

        $this->info('laravel-profane config published.');
    }
}