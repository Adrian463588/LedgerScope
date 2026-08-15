<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthorizationServiceProvider;
use App\Providers\EventServiceProvider;
use Laravel\Horizon\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    AuthorizationServiceProvider::class,
    HorizonServiceProvider::class,
];
