<?php

declare(strict_types=1);

namespace Infrastructure\XAUS\Test\Mock;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class Handler
{
    private const string RESPONSE_BODY = '{"xau": {"price": 20, "currency": "EUR", "unit": "gram"}}';

    public function __construct(private MockHandler $handler)
    {
    }

    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        $this->handler->append(new Response(200, [], self::RESPONSE_BODY));

        return $this->handler->__invoke($request, $options);
    }
}
