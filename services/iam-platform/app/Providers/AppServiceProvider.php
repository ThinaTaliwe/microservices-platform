<?php

namespace App\Providers;

use App\Authorization\Contracts\ContextAccessRepository;
use App\Authorization\Contracts\RequestContextResolver;
use App\Authorization\Resolver\SessionRequestContextResolver;
use App\Authorization\Repository\CachedContextAccessRepository;
use App\Authorization\Repository\DatabaseContextAccessRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            DatabaseContextAccessRepository::class
        );

        $this->app->singleton(
            CachedContextAccessRepository::class,
            function ($app): CachedContextAccessRepository {
                $store = config(
                    'authorization.cache.store'
                );

                $cache = $store
                    ? Cache::store((string) $store)
                    : Cache::store();

                return new CachedContextAccessRepository(
                    repository: $app->make(
                        DatabaseContextAccessRepository::class
                    ),
                    cache: $cache,
                    enabled: (bool) config(
                        'authorization.cache.enabled',
                        true
                    ),
                    ttlSeconds: (int) config(
                        'authorization.cache.ttl_seconds',
                        900
                    ),
                    prefix: (string) config(
                        'authorization.cache.prefix',
                        'iam:v2:authorization'
                    ),
                );
            }
        );

        $this->app->bind(
            ContextAccessRepository::class,
            CachedContextAccessRepository::class
        );

        $this->app->bind(
            RequestContextResolver::class,
            SessionRequestContextResolver::class
        );
    }

    public function boot(): void
    {
        //
    }
}
