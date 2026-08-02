<?php

declare(strict_types=1);

namespace Presentation\MCP\Coin;

use Application\Service\Serializer\DTO\Coin;
use Application\UseCase\Coin\Find\FindAllCoinsUseCase;
use Application\UseCase\Coin\Place\PlaceCoinUseCase;
use Application\UseCase\Coin\Place\Request\PlaceCoinRequest;
use Application\UseCase\Coin\Profitability\CalculateInventoryProfitabilityUseCase;
use Infrastructure\Validation\ConstraintsBuilder;
use Infrastructure\Validation\ValidationService;
use Mcp\Capability\Attribute\McpTool;
use Presentation\Coin\CoinInputValidator;

final readonly class CoinInventoryTools
{
    public function __construct(
        private FindAllCoinsUseCase $findAllCoins,
        private PlaceCoinUseCase $placeCoin,
        private CalculateInventoryProfitabilityUseCase $calculateInventoryProfitability,
        private ValidationService $validator,
        private ConstraintsBuilder $constraintsBuilder,
        private CoinInputValidator $coinInputValidator,
    ) {
    }

    /**
     * Returns all coins in the inventory with calculated market value fields.
     *
     * @return array{coins: array<int, array<string, float|int|string>>}
     */
    #[McpTool(
        name: 'coins_inventory_list',
        description: 'List all coins in the inventory, including purchase data and calculated metal market values.'
    )]
    public function listCoins(): array
    {
        return [
            'coins' => array_map(
                $this->coinToArray(...),
                iterator_to_array(($this->findAllCoins)())
            ),
        ];
    }

    /**
     * Returns profit and loss for each coin and portfolio totals grouped by currency.
     *
     * @return array{
     *     coins: array<int, array<string, bool|float|int|string>>,
     *     summary: array{byCurrency: array<string, array<string, bool|string>>}
     * }
     */
    #[McpTool(
        name: 'coins_inventory_profitability',
        description: 'Calculate profit and loss per coin and portfolio totals grouped by currency.'
    )]
    public function profitability(): array
    {
        return ($this->calculateInventoryProfitability)();
    }

    /**
     * Adds a coin to the inventory. purchasePrice is the minor amount, for example 286200 for 2862.00 USD.
     *
     * @return array{
     *     status: string,
     *     coin?: array<string, float|int|string>,
     *     violations?: array<int, array<string, string>>
     * }
     */
    #[McpTool(
        name: 'coins_inventory_place',
        description: 'Add a coin to the inventory. The purchasePrice argument uses minor currency units.'
    )]
    public function placeCoin(
        string $name,
        string $description,
        int $purchasePrice,
        string $purchaseCurrency,
        string $metal,
        float $weight,
        float $purity,
        int $nominal,
        string $country,
        int $year,
        string $purchaseDate,
    ): array {
        $data = [
            'country' => $country,
            'description' => $description,
            'metal' => $metal,
            'name' => $name,
            'nominal' => $nominal,
            'purchaseCurrency' => $purchaseCurrency,
            'purchaseDate' => $purchaseDate,
            'purchasePrice' => $purchasePrice,
            'purity' => $purity,
            'weight' => $weight,
            'year' => $year,
        ];

        $violations = $this->validator->validate(
            $data,
            $this->coinInputValidator->constraints($this->constraintsBuilder)
        );

        if (null !== $violations) {
            return [
                'status' => 'invalid',
                'violations' => iterator_to_array($violations),
            ];
        }

        $coin = ($this->placeCoin)(new PlaceCoinRequest(
            name: $name,
            description: $description,
            purchasePrice: $purchasePrice,
            purchaseCurrency: $purchaseCurrency,
            metal: $metal,
            weight: $weight,
            purity: $purity,
            nominal: $nominal,
            country: $country,
            year: $year,
            purchaseDate: $purchaseDate
        ));

        return [
            'coin' => $this->coinToArray($coin),
            'status' => 'created',
        ];
    }

    /**
     * @return array<string, float|int|string>
     */
    private function coinToArray(Coin $coin): array
    {
        $data = get_object_vars($coin);
        ksort($data);

        return $data;
    }
}
