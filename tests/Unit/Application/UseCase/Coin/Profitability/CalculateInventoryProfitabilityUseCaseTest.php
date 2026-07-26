<?php

declare(strict_types=1);

namespace Application\UseCase\Coin\Profitability;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Infrastructure\Test\TestCase;

final class CalculateInventoryProfitabilityUseCaseTest extends TestCase
{
    public function testShouldCalculateInventoryProfitability(): void
    {
        /** @var CalculateInventoryProfitabilityUseCase $useCase */
        $useCase = $this->getContainer()->get(CalculateInventoryProfitabilityUseCase::class);

        $report = $useCase();

        $this->assertSame(
            [
                [
                    'coinId' => '0193ce8c-5e87-7f73-81de-b17ef561d33c',
                    'coinName' => 'Britannia 1/2 oz Gold',
                    'currency' => 'EUR',
                    'isProfitable' => false,
                    'marketMetalPriceValue' => '310.97',
                    'profitLossPercent' => '-79.94',
                    'profitLossValue' => '-1239.03',
                    'purchasePrice' => '1550.00',
                ],
                [
                    'coinId' => '01a4bd7c-9f34-4e22-94af-1134ae561f77',
                    'coinName' => 'Golden Eagle',
                    'currency' => 'EUR',
                    'isProfitable' => false,
                    'marketMetalPriceValue' => '570.13',
                    'profitLossPercent' => '-77.19',
                    'profitLossValue' => '-1929.87',
                    'purchasePrice' => '2500.00',
                ],
                [
                    'coinId' => '02b7de8f-8e45-5c11-82bc-b38ecf671a55',
                    'coinName' => 'Maple Leaf',
                    'currency' => 'EUR',
                    'isProfitable' => false,
                    'marketMetalPriceValue' => '621.94',
                    'profitLossPercent' => '-67.27',
                    'profitLossValue' => '-1278.06',
                    'purchasePrice' => '1900.00',
                ],
            ],
            $this->sortCoins($report['coins'])
        );

        $this->assertSame(
            [
                'EUR' => [
                    'isProfitable' => false,
                    'marketValue' => '1503.04',
                    'profitLossPercent' => '-74.74',
                    'profitLossValue' => '-4446.96',
                    'purchaseValue' => '5950.00',
                ],
            ],
            $report['summary']['byCurrency']
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadCoinsFixture();

        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/../Find/Response/market.json')),
            new Response(200, [], file_get_contents(__DIR__ . '/../Find/Response/market.json')),
            new Response(200, [], file_get_contents(__DIR__ . '/../Find/Response/market.json')),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $this->getContainer()->set('guzzle.xaus.client', $mockClient);
    }

    private function sortCoins(array $coins): array
    {
        usort(
            $coins,
            static fn (array $left, array $right): int => $left['coinName'] <=> $right['coinName']
        );

        return $coins;
    }
}
