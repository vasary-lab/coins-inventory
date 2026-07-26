<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Coin\Repository\RepositoryInterface;
use Infrastructure\Test\Fixture\CoinFixture;
use Infrastructure\Test\TestCase;
use Predis\ClientInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class InventoryFeatureTest extends TestCase
{
    private KernelBrowser $client;

    public function testShouldCreateNewCoin(): void
    {
        $this->client->jsonRequest(
            method: 'PUT',
            uri: '/api/inventory',
            parameters: [
                'country' => 'usa',
                'description' => 'American Gold Eagle 1 oz',
                'metal' => 'Gold',
                'name' => 'American Eagle',
                'nominal' => 50,
                'purchaseCurrency' => 'USD',
                'purchaseDate' => '2025-03-10',
                'purchasePrice' => 286200,
                'purity' => 91.67,
                'weight' => 31.1,
                'year' => 2025,
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = $this->responseData();

        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $response['id']);
        unset($response['id']);
        ksort($response);

        $this->assertSame([
            'country' => 'usa',
            'description' => 'American Gold Eagle 1 oz',
            'karats' => 22,
            'marketMetalPriceValue' => '0.00',
            'metal' => 'gold',
            'name' => 'American Eagle',
            'nominal' => 50,
            'purchaseCurrency' => 'USD',
            'purchaseDate' => '2025-03-10T00:00:00+00:00',
            'purchasePrice' => '2862.00',
            'pureMetalWeight' => 28.509370000000004,
            'purity' => 91.67,
            'weight' => 31.1,
            'year' => 2025,
        ], $response);
    }

    public function testShouldGetCoinsList(): void
    {
        $this->loadCoins('eagle', 'mapleLeaf', 'britannia');

        $this->client->request('GET', '/api/inventory');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = $this->responseData();

        foreach ($response as $coin) {
            $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $coin['id']);
        }

        $normalizedResponse = $this->normalizeCoins($response);

        $this->assertSame($this->normalizeCoins([
            [
                'country' => 'australia',
                'description' => 'Iconic Canadian gold coin',
                'karats' => 24,
                'marketMetalPriceValue' => '621.94',
                'metal' => 'gold',
                'name' => 'Maple Leaf',
                'nominal' => 50,
                'purchaseCurrency' => 'EUR',
                'purchaseDate' => '2023-10-20T09:45:00+02:00',
                'purchasePrice' => '1900.00',
                'pureMetalWeight' => 31.09689,
                'purity' => 99.99,
                'weight' => 31.1,
                'year' => 2022,
            ],
            [
                'country' => 'unitedKingdom',
                'description' => 'Gold investment coin',
                'karats' => 24,
                'marketMetalPriceValue' => '310.97',
                'metal' => 'gold',
                'name' => 'Britannia 1/2 oz Gold',
                'nominal' => 50,
                'purchaseCurrency' => 'EUR',
                'purchaseDate' => '2023-12-01T00:15:00+02:00',
                'purchasePrice' => '1550.00',
                'pureMetalWeight' => 15.548445,
                'purity' => 99.99,
                'weight' => 15.55,
                'year' => 2024,
            ],
            [
                'country' => 'usa',
                'description' => 'A stunning silver coin from the USA',
                'karats' => 22,
                'marketMetalPriceValue' => '570.13',
                'metal' => 'gold',
                'name' => 'Golden Eagle',
                'nominal' => 1,
                'purchaseCurrency' => 'EUR',
                'purchaseDate' => '2023-11-15T14:30:00+02:00',
                'purchasePrice' => '2500.00',
                'pureMetalWeight' => 31.103631,
                'purity' => 91.67,
                'weight' => 33.93,
                'year' => 2023,
            ],
        ]), $normalizedResponse);
    }

    public function testShouldGetInventoryProfitability(): void
    {
        $this->loadCoins('eagle', 'mapleLeaf', 'britannia');

        $this->client->request('GET', '/api/inventory/profitability');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = $this->responseData();
        $response['coins'] = $this->sortProfitabilityCoins($response['coins']);

        $this->assertSame([
            'coins' => [
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
            'summary' => [
                'byCurrency' => [
                    'EUR' => [
                        'isProfitable' => false,
                        'marketValue' => '1503.04',
                        'profitLossPercent' => '-74.74',
                        'profitLossValue' => '-4446.96',
                        'purchaseValue' => '5950.00',
                    ],
                ],
            ],
        ], $response);
    }

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = self::createClient();
        $this->getContainer()->get(ClientInterface::class)->flushAll();
    }

    protected function tearDown(): void
    {
        $this->getContainer()->get(ClientInterface::class)->flushAll();

        parent::tearDown();
    }

    private function loadCoins(string ...$coins): void
    {
        /** @var RepositoryInterface $repository */
        $repository = $this->getContainer()->get(RepositoryInterface::class);

        /** @var CoinFixture $fixture */
        $fixture = $this->getContainer()->get(CoinFixture::class);

        foreach ($coins as $coin) {
            $repository->add($fixture->$coin());
        }
    }

    private function responseData(): array
    {
        $content = $this->client->getResponse()->getContent();

        if (false === $content) {
            $content = $this->client->getInternalResponse()->getContent();
        }

        $this->assertJson($content);

        return json_decode($content, true, flags: JSON_THROW_ON_ERROR);
    }

    private function normalizeCoins(array $coins): array
    {
        $normalizedCoins = array_map(
            static function (array $coin): array {
                unset($coin['id']);
                ksort($coin);

                return $coin;
            },
            $coins
        );

        usort(
            $normalizedCoins,
            static fn (array $left, array $right): int => $left['name'] <=> $right['name']
        );

        return $normalizedCoins;
    }

    private function sortProfitabilityCoins(array $coins): array
    {
        usort(
            $coins,
            static fn (array $left, array $right): int => $left['coinName'] <=> $right['coinName']
        );

        return $coins;
    }
}
