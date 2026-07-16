<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Resolver\CachedComponentReferenceResolver;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\TestCase;

class CachedComponentReferenceResolverTest extends TestCase
{
    public function test_it_normalizes_and_resolves_reference(): void
    {
        $database = $this->createMock(
            ConnectionInterface::class
        );

        $query = new class {
            public function where(
                string $column,
                mixed $value
            ): self {
                return $this;
            }

            public function value(
                string $column
            ): int {
                return 15;
            }
        };

        $database
            ->expects($this->once())
            ->method('table')
            ->with('access_components')
            ->willReturn($query);

        $resolver = $this->resolver($database);

        $this->assertSame(
            15,
            $resolver->resolve(' IAM-Sessions ')
        );

        $this->assertSame(
            15,
            $resolver->resolve('iam-sessions')
        );
    }

    public function test_unknown_reference_returns_null(): void
    {
        $database = $this->createMock(
            ConnectionInterface::class
        );

        $query = new class {
            public function where(
                string $column,
                mixed $value
            ): self {
                return $this;
            }

            public function value(
                string $column
            ): mixed {
                return null;
            }
        };

        $database
            ->method('table')
            ->willReturn($query);

        $resolver = $this->resolver($database);

        $this->assertNull(
            $resolver->resolve('unknown-component')
        );
    }

    private function resolver(
        ConnectionInterface $database
    ): CachedComponentReferenceResolver {
        return new CachedComponentReferenceResolver(
            database: $database,
            cache: new CacheRepository(
                new ArrayStore()
            ),
            ttlSeconds: 900,
            prefix: 'test:component-reference',
        );
    }
}
