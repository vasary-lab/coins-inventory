<?php

declare(strict_types=1);

namespace Infrastructure\XAUS\Guzzle;

use Domain\Common\Enum\Metal;
use Domain\Common\ValueObject\Money;
use Domain\Market\Repository\MarketRepositoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Infrastructure\Money\Service\MoneyInitializerService;
use OutOfBoundsException;

final readonly class XausRepository implements MarketRepositoryInterface
{
    public function __construct(
        private ClientInterface $client,
        private MoneyInitializerService $moneyInitializer,
    ) {
    }

    public function metalPricePerGram(Metal $metal, int $karat, string $currency): Money
    {
        $this->resolveMetalCode($metal);

        return $this->fetchMetalPrice($karat, $currency);
    }

    private function resolveMetalCode(Metal $metal): string
    {
        return match ($metal) {
            Metal::Gold => 'XAU',
        };
    }

    private function fetchMetalPrice(int $karat, string $currency): Money
    {
        return $this->client->getAsync('spot', [
            'query' => [
                'currency' => $currency,
                'unit' => 'gram',
            ],
        ])
            ->then(
                fn (Response $response) => $this->parseResponse($response, $karat, $currency),
                fn (GuzzleException | OutOfBoundsException $e) => $this->handleRequestException($e, $currency)
            )
            ->wait();
    }

    private function parseResponse(Response $response, int $karat, string $currency): Money
    {
        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['xau']['price'])) {
            return $this->handleRequestException(new OutOfBoundsException('Missing XAU gram price'), $currency);
        }

        $price = (int) round($data['xau']['price'] * $karat / 24 * 100);

        return $this->moneyInitializer->create($price, $currency);
    }

    private function handleRequestException(GuzzleException | OutOfBoundsException $exception, string $currency): Money
    {
        return $this->moneyInitializer->create(0, $currency);
    }
}
