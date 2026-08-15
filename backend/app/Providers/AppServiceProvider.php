<?php

namespace App\Providers;

use App\Services\Integrations\AiIntegrationAdapter;
use App\Services\Integrations\AnomalyIntegrationAdapter;
use App\Services\Integrations\BankingIntegrationAdapter;
use App\Services\Integrations\ErpIntegrationAdapter;
use App\Services\Integrations\IntegrationRegistry;
use App\Services\Integrations\InventoryIntegrationAdapter;
use App\Services\Integrations\MobileSyncIntegrationAdapter;
use App\Services\Integrations\OcrIntegrationAdapter;
use App\Services\Integrations\PayrollIntegrationAdapter;
use App\Services\Integrations\SsoIntegrationAdapter;
use App\Services\Integrations\TaxIntegrationAdapter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(IntegrationRegistry::class, static fn (): IntegrationRegistry => new IntegrationRegistry([
            new ErpIntegrationAdapter,
            new PayrollIntegrationAdapter,
            new InventoryIntegrationAdapter,
            new TaxIntegrationAdapter,
            new BankingIntegrationAdapter,
            new SsoIntegrationAdapter,
            new OcrIntegrationAdapter,
            new AiIntegrationAdapter,
            new MobileSyncIntegrationAdapter,
            new AnomalyIntegrationAdapter,
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
