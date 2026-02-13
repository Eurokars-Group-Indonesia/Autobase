<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestAzureConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azure:test-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Azure AD configuration and display current settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Azure AD Configuration Test ===');
        $this->newLine();
        
        // Get configuration
        $tenant = config('services.azure.tenant');
        $tenantId = config('services.azure.tenant_id');
        $clientId = config('services.azure.client_id');
        $clientSecret = config('services.azure.client_secret');
        $redirect = config('services.azure.redirect');
        
        // Display configuration
        $this->info('Current Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Tenant', $tenant ?: '<not set>'],
                ['Tenant ID', $tenantId ?: '<not set>'],
                ['Client ID', $clientId ?: '<not set>'],
                ['Client Secret', $clientSecret ? str_repeat('*', 20) . substr($clientSecret, -4) : '<not set>'],
                ['Redirect URI', $redirect ?: '<not set>'],
            ]
        );
        
        $this->newLine();
        
        // Validation
        $this->info('Validation:');
        $errors = [];
        
        if (empty($tenant)) {
            $errors[] = '❌ Tenant is not configured';
        } elseif ($tenant === 'common') {
            $errors[] = '⚠️  Tenant is set to "common" - this may cause AADSTS50194 error for single-tenant apps';
        } else {
            $this->line('✅ Tenant is configured: ' . $tenant);
        }
        
        if (empty($clientId)) {
            $errors[] = '❌ Client ID is not configured';
        } else {
            $this->line('✅ Client ID is configured');
        }
        
        if (empty($clientSecret)) {
            $errors[] = '❌ Client Secret is not configured';
        } else {
            $this->line('✅ Client Secret is configured');
        }
        
        if (empty($redirect)) {
            $errors[] = '❌ Redirect URI is not configured';
        } else {
            $this->line('✅ Redirect URI is configured');
        }
        
        $this->newLine();
        
        // Expected URLs
        if (!empty($tenant) && $tenant !== 'common') {
            $this->info('Expected Azure Endpoints:');
            $this->line('Authorization URL: https://login.microsoftonline.com/' . $tenant . '/oauth2/v2.0/authorize');
            $this->line('Token URL: https://login.microsoftonline.com/' . $tenant . '/oauth2/v2.0/token');
            $this->newLine();
        }
        
        // Display errors
        if (!empty($errors)) {
            $this->newLine();
            $this->error('Configuration Issues:');
            foreach ($errors as $error) {
                $this->line($error);
            }
            $this->newLine();
            $this->warn('Please fix the configuration issues in your .env file and run: php artisan config:clear');
            return 1;
        }
        
        $this->newLine();
        $this->info('✅ Configuration looks good!');
        $this->newLine();
        
        // Additional checks
        $this->info('Additional Information:');
        $this->line('App URL: ' . config('app.url'));
        $this->line('Environment: ' . config('app.env'));
        $this->newLine();
        
        // Recommendations
        $this->info('Recommendations:');
        $this->line('1. Ensure the Redirect URI in Azure Portal matches: ' . $redirect);
        $this->line('2. Ensure API permissions are granted in Azure Portal:');
        $this->line('   - Microsoft Graph → User.Read (Delegated)');
        $this->line('   - Microsoft Graph → User.Read.All (Delegated)');
        $this->line('   - Microsoft Graph → offline_access (Delegated)');
        $this->line('3. Ensure "Accounts in this organizational directory only" is selected');
        $this->line('4. After any .env changes, run: php artisan config:clear');
        
        return 0;
    }
}
