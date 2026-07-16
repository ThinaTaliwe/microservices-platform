<?php

namespace App\Authorization\Resolver;

use App\Authorization\Contracts\ComponentReferenceResolver;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\ConnectionInterface;

class CachedComponentReferenceResolver implements
    ComponentReferenceResolver
{
    public function __construct(
        private readonly ConnectionInterface $database,
        private readonly CacheRepository $cache,
        private readonly int $ttlSeconds = 3600,
        private readonly string $prefix =
            'iam:v2:component-reference',
    ) {
    }

    public function resolve(
        string $reference
    ): ?int {
        $reference = $this->normalize($reference);

        if ($reference === '') {
            return null;
        }

        $value = $this->cache->remember(
            $this->key($reference),
            max(1, $this->ttlSeconds),
            function () use ($reference): int {
                $id = $this->database
                    ->table('access_components')
                    ->where(
                        'external_key',
                        $reference
                    )
                    ->where('status', 'active')
                    ->value('id');

                return $id === null
                    ? 0
                    : (int) $id;
            }
        );

        $id = (int) $value;

        return $id > 0
            ? $id
            : null;
    }

    public function forget(
        ?string $reference = null
    ): void {
        if ($reference === null) {
            return;
        }

        $reference = $this->normalize($reference);

        if ($reference !== '') {
            $this->cache->forget(
                $this->key($reference)
            );
        }
    }

    private function normalize(
        string $reference
    ): string {
        return strtolower(trim($reference));
    }

    private function key(
        string $reference
    ): string {
        return implode(':', [
            $this->prefix,
            hash('sha256', $reference),
        ]);
    }
}
