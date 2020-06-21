<?php

namespace Zedu\IwsGraphqlLogin\Providers;

use Illuminate\Support\ServiceProvider;
use Zedu\IwsGraphqlLogin\Contracts\AuthModelFactory as AuthModelFactoryContract;
use Zedu\IwsGraphqlLogin\Factories\AuthModelFactory;
use Zedu\IwsGraphqlLogin\OAuthGrants\LoggedInGrant;
use Zedu\IwsGraphqlLogin\OAuthGrants\SocialGrant;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use Nuwave\Lighthouse\Events\BuildSchemaString;

/**
 * Class IwsGraphqlLoginServiceProvider.
 */
class IwsGraphqlLoginServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('iws-graphql-login.migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }
    }

    public function register()
    {
        $this->app->singleton(AuthModelFactoryContract::class, AuthModelFactory::class);

        $this->extendAuthorizationServer();
        $this->registerConfig();

        app('events')->listen(
            BuildSchemaString::class,
            function (): string {
                if (config('iws-graphql-login.schema')) {
                    return file_get_contents(config('iws-graphql-login.schema'));
                }

                return file_get_contents(__DIR__ . '/../../graphql/auth.graphql');
            }
        );
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php',
            'iws-graphql-login'
        );

        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('iws-graphql-login.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../graphql/auth.graphql' => base_path('graphql/auth.graphql'),
        ], 'schema');

        $this->publishes([
            __DIR__ . '/../../migrations/2019_11_19_000000_update_social_provider_users_table.php' => base_path('database/migrations/2019_11_19_000000_update_social_provider_users_table.php'),
        ], 'migrations');
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return SocialGrant
     */
    protected function makeCustomRequestGrant()
    {
        $grant = new SocialGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );
        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return LoggedInGrant
     */
    protected function makeLoggedInRequestGrant()
    {
        $grant = new LoggedInGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );
        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }

    /**
     * Register the Grants.
     *
     * @return void
     */
    protected function extendAuthorizationServer()
    {
        $this->app->extend(AuthorizationServer::class, function ($server) {
            return tap($server, function ($server) {
                $server->enableGrantType(
                    $this->makeLoggedInRequestGrant(),
                    Passport::tokensExpireIn()
                );

                $server->enableGrantType(
                    $this->makeCustomRequestGrant(),
                    Passport::tokensExpireIn()
                );
            });
        });
    }
}
