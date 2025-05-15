<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class DebugHubApiConfig extends Command
{
    protected $signature = 'debug:hub-api-config';
    protected $description = 'Debug HUB API configuration';

    public function handle()
    {
        $this->info('Debugging HUB API Configuration');
        $this->info('===============================');
        
        // Check environment variables
        $this->info("\nChecking environment variables:");
        $this->line("HUB_API_CLIENT_ID: " . (env('HUB_API_CLIENT_ID') ? substr(env('HUB_API_CLIENT_ID'), 0, 5) . '...' : 'Not set'));
        $this->line("HUB_API_CLIENT_SECRET: " . (env('HUB_API_CLIENT_SECRET') ? 'Set (hidden)' : 'Not set'));
        $this->line("HUB_API_ENDPOINT: " . env('HUB_API_ENDPOINT', 'Not set (using default)'));
        
        // Check config values
        $this->info("\nChecking config values:");
        $this->line("hubapi.client_id: " . (config('hubapi.client_id') ? substr(config('hubapi.client_id'), 0, 5) . '...' : 'Not set'));
        $this->line("hubapi.client_secret: " . (config('hubapi.client_secret') ? 'Set (hidden)' : 'Not set'));
        $this->line("hubapi.endpoint: " . config('hubapi.endpoint', 'Not set (using default)'));
        
        // Check if config file exists
        $configPath = config_path('hubapi.php');
        $this->info("\nChecking config file:");
        $this->line("Config file path: " . $configPath);
        $this->line("Config file exists: " . (file_exists($configPath) ? 'Yes' : 'No'));
        
        // Check .env file
        $envPath = base_path('.env');
        $this->info("\nChecking .env file:");
        $this->line("Env file path: " . $envPath);
        $this->line("Env file exists: " . (file_exists($envPath) ? 'Yes' : 'No'));
        
        if (file_exists($envPath)) {
            $envContents = file_get_contents($envPath);
            $this->line("\nSearching for HUB_API_ variables in .env file:");
            
            if (preg_match_all('/HUB_API_[A-Z_]+=.+/', $envContents, $matches)) {
                foreach ($matches[0] as $match) {
                    // Hide sensitive values
                    if (strpos($match, 'SECRET') !== false || strpos($match, 'KEY') !== false) {
                        $parts = explode('=', $match, 2);
                        $this->line($parts[0] . '=[HIDDEN]');
                    } else {
                        $this->line($match);
                    }
                }
            } else {
                $this->line("No HUB_API_ variables found in .env file");
            }
        }
        
        $this->info("\nConfiguration checking complete.");
        
        return 0;
    }
}