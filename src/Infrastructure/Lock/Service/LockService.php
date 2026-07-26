<?php

declare(strict_types=1);

namespace Infrastructure\Lock\Service;

use Application\Service\LockService\LockServiceInterface;
use Closure;
use Malkusch\Lock\Mutex\RedisMutex;
use Predis\Client;

final readonly class LockService implements LockServiceInterface
{
    private const string NAMESPACE = 'lock:{id}';

    public function __construct(private Client $client)
    {
    }

    public function runExclusive(Closure $closure, string $key): mixed
    {
        $id = str_replace('{id}', $key, self::NAMESPACE);

        return new RedisMutex($this->client, $id)->synchronized($closure);
    }
}
