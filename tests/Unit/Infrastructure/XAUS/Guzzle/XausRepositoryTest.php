<?php

declare(strict_types=1);

namespace Infrastructure\XAUS\Guzzle;

use Domain\Common\Enum\Metal;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Infrastructure\Money\Service\MoneyInitializerService;
use Infrastructure\Test\TestCase;

final class XausRepositoryTest extends TestCase
{
    public function testShouldReturnXausSuccessfullyResult(): void
    {
        $clientMock = $this->getClientMock(new Response(200, body: json_encode([
            'xau' => [
                'currency' => 'EUR',
                'price' => 120,
                'unit' => 'gram',
            ],
        ])));

        $moneyInitializer = new MoneyInitializerService();

        $repository = new XausRepository($clientMock, $moneyInitializer);

        $price = $repository->metalPricePerGram(Metal::Gold, 22, 'EUR');

        $this->assertEquals('110.00', $price->getAmount());
        $this->assertEquals('EUR', $price->getCurrency());
    }

    public function testShouldReturnZeroWhenXausFails(): void
    {
        $clientMock = $this->getClientMock(
            new RequestException('Synthetic exception', new Request('GET', 'test'))
        );

        $moneyInitializer = new MoneyInitializerService();

        $repository = new XausRepository($clientMock, $moneyInitializer);

        $price = $repository->metalPricePerGram(Metal::Gold, 22, 'EUR');

        $this->assertEquals('0.00', $price->getAmount());
        $this->assertEquals('EUR', $price->getCurrency());
    }

    private function getClientMock(Response|RequestException ...$response): ClientInterface
    {
        return new Client([
            'handler' => new MockHandler($response),
        ]);
    }
}
