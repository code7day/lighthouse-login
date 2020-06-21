<?php

namespace Zedu\IwsGraphqlLogin\Tests;

use Laravel\Passport\Passport;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(realpath(__DIR__ . '/../tests/migrations'));
        Passport::routes();
        Passport::loadKeysFrom(__DIR__ . '/storage/');
        config()->set('lighthouse.route.middleware', [
            \Nuwave\Lighthouse\Support\Http\Middleware\AcceptJson::class,
            \Zedu\IwsGraphqlLogin\Http\Middleware\AuthenticateWithApiGuard::class,
        ]);
    }
}
