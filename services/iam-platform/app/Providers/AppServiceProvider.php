<?php

namespace App\Providers;

use App\Authorization\Contracts\AuthorizationCacheInvalidator;
use App\Authorization\Contracts\ComponentReferenceResolver;
use App\Authorization\Resolver\CachedComponentReferenceResolver;
use App\Authorization\Contracts\ContextAccessRepository;
use App\Authorization\Contracts\ContextRoleAssignmentRepository;
use App\Authorization\Contracts\ContextRoleAssignmentQueryRepository;
use App\Authorization\Contracts\RequestContextResolver;
use App\Authorization\Resolver\SessionRequestContextResolver;
use App\Authorization\Repository\CachedContextAccessRepository;
use App\Authorization\Repository\DatabaseContextAccessRepository;
use App\Authorization\Repository\DatabaseContextRoleAssignmentRepository;
use App\Authorization\Repository\DatabaseContextRoleAssignmentQueryRepository;
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
            AuthorizationCacheInvalidator::class,
            CachedContextAccessRepository::class
        );

        $this->app->singleton(
            DatabaseContextRoleAssignmentRepository::class
        );

        $this->app->bind(
            ContextRoleAssignmentRepository::class,
            DatabaseContextRoleAssignmentRepository::class
        );

        $this->app->singleton(
            DatabaseContextRoleAssignmentQueryRepository::class
        );

        $this->app->bind(
            ContextRoleAssignmentQueryRepository::class,
            DatabaseContextRoleAssignmentQueryRepository::class
        );

        $this->app->bind(
            RequestContextResolver::class,
            SessionRequestContextResolver::class
        );

        $this->app->singleton(
            ComponentReferenceResolver::class,
            function (): CachedComponentReferenceResolver {
                $store = config(
                    'authorization.cache.store'
                );

                $cache = $store
                    ? Cache::store((string) $store)
                    : Cache::store();

                return new CachedComponentReferenceResolver(
                    database: app('db')->connection(),
                    cache: $cache,
                    ttlSeconds: (int) config(
                        'authorization.cache.ttl_seconds',
                        900
                    ),
                    prefix: (string) config(
                        'authorization.cache.prefix',
                        'iam:v2:authorization'
                    ) . ':component-reference',
                );
            }
        );
    }

    public function boot(): void
    {
        //
    }
}
