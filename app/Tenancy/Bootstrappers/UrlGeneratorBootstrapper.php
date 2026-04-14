<?php

namespace App\Tenancy\Bootstrappers;

use Illuminate\Support\Facades\URL;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * URL Generator Bootstrapper for Tenant Context
 * 
 * This bootstrapper ensures that URLs generated within tenant context
 * (especially in queued jobs/notifications) use the correct tenant domain
 * instead of falling back to APP_URL (central domain).
 * 
 * Fixes issue where notifications in queued jobs show central.lvh.me
 * instead of the actual tenant's subdomain.
 */
class UrlGeneratorBootstrapper implements TenancyBootstrapper
{
    protected $originalUrl;

    /**
     * Bootstrap tenancy - set URL generator to use tenant's domain
     */
    public function bootstrap(Tenant $tenant)
    {
        $domain = $tenant->domains()->first();
        
        if ($domain) {
            // Store original URL for revert
            $this->originalUrl = config('app.url');
            
            // Parse APP_URL to preserve protocol and port
            $protocol = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';
            $port = parse_url(config('app.url'), PHP_URL_PORT);
            
            // Build tenant URL
            // Examples:
            //   Local: http://schoolname.lvh.me:8000
            //   Prod:  https://schoolname.example.com
            $tenantUrl = "{$protocol}://{$domain->domain}" . ($port ? ":{$port}" : '');
            
            // Update config and force URL generator to use tenant domain
            config(['app.url' => $tenantUrl]);
            URL::forceRootUrl($tenantUrl);
        }
    }

    /**
     * Revert tenancy - restore original URL configuration
     */
    public function revert()
    {
        if ($this->originalUrl) {
            config(['app.url' => $this->originalUrl]);
            URL::forceRootUrl($this->originalUrl);
        }
    }
}
