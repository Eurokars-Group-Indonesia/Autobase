<?php

namespace App\Services;

use SocialiteProviders\Microsoft\Provider as MicrosoftProvider;

class CustomAzureProvider extends MicrosoftProvider
{
    /**
     * Override getAuthUrl to ensure tenant-specific endpoint is used
     */
    protected function getAuthUrl($state): string
    {
        $tenant = $this->getConfig('tenant', config('services.azure.tenant', 'common'));
        
        // Force tenant-specific endpoint, never use 'common'
        if ($tenant === 'common' || empty($tenant)) {
            $tenant = config('services.azure.tenant_id', config('services.azure.tenant'));
        }
        
        \Log::info('CustomAzureProvider: Building auth URL', [
            'tenant' => $tenant,
            'config_tenant' => $this->getConfig('tenant'),
            'env_tenant' => config('services.azure.tenant'),
            'env_tenant_id' => config('services.azure.tenant_id')
        ]);
        
        return $this->buildAuthUrlFromBase(
            sprintf(
                'https://login.microsoftonline.com/%s/oauth2/v2.0/authorize',
                $tenant
            ),
            $state
        );
    }
    
    /**
     * Override getTokenUrl to ensure tenant-specific endpoint is used
     */
    protected function getTokenUrl(): string
    {
        $tenant = $this->getConfig('tenant', config('services.azure.tenant', 'common'));
        
        // Force tenant-specific endpoint, never use 'common'
        if ($tenant === 'common' || empty($tenant)) {
            $tenant = config('services.azure.tenant_id', config('services.azure.tenant'));
        }
        
        return sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/token', $tenant);
    }
}
