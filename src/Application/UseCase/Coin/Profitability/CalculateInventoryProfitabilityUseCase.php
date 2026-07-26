<?php

declare(strict_types=1);

namespace Application\UseCase\Coin\Profitability;

use Domain\Coin\Repository\RepositoryInterface;
use Domain\Market\Service\MetalMarket;

final readonly class CalculateInventoryProfitabilityUseCase
{
    public function __construct(
        private RepositoryInterface $repository,
        private MetalMarket $market,
    ) {
    }

    private function toMinorUnits(string $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function formatMinorUnits(int $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $absoluteAmount = abs($amount);

        return sprintf('%s%d.%02d', $sign, intdiv($absoluteAmount, 100), $absoluteAmount % 100);
    }

    private function profitLossPercent(int $profitLoss, int $purchasePrice): string
    {
        if (0 === $purchasePrice) {
            return '0.00';
        }

        return number_format($profitLoss / $purchasePrice * 100, 2, '.', '');
    }

    /**
     * @param array<string, array{marketValue: int, profitLoss: int, purchaseValue: int}> $summary
     *
     * @return array<string, array<string, bool|string>>
     */
    private function summary(array $summary): array
    {
        $result = [];

        foreach ($summary as $currency => $currencySummary) {
            $result[$currency] = [
                'isProfitable' => $currencySummary['profitLoss'] > 0,
                'marketValue' => $this->formatMinorUnits($currencySummary['marketValue']),
                'profitLossPercent' => $this->profitLossPercent(
                    $currencySummary['profitLoss'],
                    $currencySummary['purchaseValue']
                ),
                'profitLossValue' => $this->formatMinorUnits($currencySummary['profitLoss']),
                'purchaseValue' => $this->formatMinorUnits($currencySummary['purchaseValue']),
            ];
        }

        ksort($result);

        return $result;
    }

    /**
     * @return array{
     *     coins: array<int, array<string, bool|float|int|string>>,
     *     summary: array{byCurrency: array<string, array<string, bool|string>>}
     * }
     */
    public function __invoke(): array
    {
        $coins = [];
        $summary = [];

        foreach ($this->repository->finaAll() as $coin) {
            $currency = $coin->purchasePrice->getCurrency();
            $purchasePrice = $this->toMinorUnits($coin->purchasePrice->getAmount());
            $marketValue = $this->toMinorUnits($this->market->evaluate($coin)->getAmount());
            $profitLoss = $marketValue - $purchasePrice;

            $coins[] = [
                'coinId' => (string) $coin->id,
                'coinName' => (string) $coin->name,
                'currency' => $currency,
                'isProfitable' => $profitLoss > 0,
                'marketMetalPriceValue' => $this->formatMinorUnits($marketValue),
                'profitLossPercent' => $this->profitLossPercent($profitLoss, $purchasePrice),
                'profitLossValue' => $this->formatMinorUnits($profitLoss),
                'purchasePrice' => $this->formatMinorUnits($purchasePrice),
            ];

            $summary[$currency]['marketValue'] = ($summary[$currency]['marketValue'] ?? 0) + $marketValue;
            $summary[$currency]['profitLoss'] = ($summary[$currency]['profitLoss'] ?? 0) + $profitLoss;
            $summary[$currency]['purchaseValue'] = ($summary[$currency]['purchaseValue'] ?? 0) + $purchasePrice;
        }

        return [
            'coins' => $coins,
            'summary' => [
                'byCurrency' => $this->summary($summary),
            ],
        ];
    }
}
