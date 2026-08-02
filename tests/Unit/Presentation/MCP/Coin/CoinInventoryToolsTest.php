<?php

declare(strict_types=1);

namespace Presentation\MCP\Coin;

use Application\Service\Serializer\DTO\Coin;
use Application\UseCase\Coin\Find\FindAllCoinsUseCase;
use Application\UseCase\Coin\Place\PlaceCoinUseCase;
use Application\UseCase\Coin\Profitability\CalculateInventoryProfitabilityUseCase;
use Domain\Common\Enum\Country;
use Domain\Common\Enum\Metal;
use Generator;
use Infrastructure\Validation\ConstraintsBuilder;
use Infrastructure\Validation\ValidationService;
use PHPUnit\Framework\TestCase;
use Presentation\Coin\CoinInputValidator;
use Symfony\Component\Validator\Validation;

final class CoinInventoryToolsTest extends TestCase
{
    public function testShouldListCoins(): void
    {
        $findAllCoins = $this->createMock(FindAllCoinsUseCase::class);
        $findAllCoins
            ->expects($this->once())
            ->method('__invoke')
            ->willReturnCallback(
                fn (): Generator => yield $this->coin()
            );

        $tools = new CoinInventoryTools(
            findAllCoins: $findAllCoins,
            placeCoin: $this->unusedPlaceCoinUseCase(),
            calculateInventoryProfitability: $this->unusedCalculateInventoryProfitabilityUseCase(),
            validator: $this->validationService(),
            constraintsBuilder: new ConstraintsBuilder(),
            coinInputValidator: new CoinInputValidator()
        );

        $result = $tools->listCoins();

        $this->assertMcpStructuredContentRecord($result);
        $this->assertSame(['coins' => [$this->coinData()]], $result);
    }

    public function testShouldPlaceCoin(): void
    {
        $placeCoin = $this->createMock(PlaceCoinUseCase::class);
        $placeCoin
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($this->coin());

        $tools = new CoinInventoryTools(
            findAllCoins: $this->unusedFindAllCoinsUseCase(),
            placeCoin: $placeCoin,
            calculateInventoryProfitability: $this->unusedCalculateInventoryProfitabilityUseCase(),
            validator: $this->validationService(),
            constraintsBuilder: new ConstraintsBuilder(),
            coinInputValidator: new CoinInputValidator()
        );

        $result = $tools->placeCoin(
            name: 'name',
            description: 'description',
            purchasePrice: 1000,
            purchaseCurrency: 'EUR',
            metal: 'Gold',
            weight: 10,
            purity: 99.999,
            nominal: 25,
            country: 'usa',
            year: 2015,
            purchaseDate: '2023-12-01'
        );

        $this->assertMcpStructuredContentRecord($result);
        $this->assertSame(
            [
                'coin' => $this->coinData(),
                'status' => 'created',
            ],
            $result
        );
    }

    public function testShouldReturnValidationViolations(): void
    {
        $placeCoin = $this->createMock(PlaceCoinUseCase::class);
        $placeCoin
            ->expects($this->never())
            ->method('__invoke');

        $tools = new CoinInventoryTools(
            findAllCoins: $this->unusedFindAllCoinsUseCase(),
            placeCoin: $placeCoin,
            calculateInventoryProfitability: $this->unusedCalculateInventoryProfitabilityUseCase(),
            validator: $this->validationService(),
            constraintsBuilder: new ConstraintsBuilder(),
            coinInputValidator: new CoinInputValidator()
        );

        $result = $tools->placeCoin(
            name: 'name',
            description: 'description',
            purchasePrice: 1000,
            purchaseCurrency: 'EUR',
            metal: 'Gold',
            weight: 10,
            purity: 99.999,
            nominal: 25,
            country: 'mars',
            year: 2015,
            purchaseDate: '2023-12-01'
        );

        $this->assertMcpStructuredContentRecord($result);
        $this->assertSame(
            [
                'status' => 'invalid',
                'violations' => [
                    ['field' => '[country]', 'message' => 'The value you selected is not a valid choice.'],
                ],
            ],
            $result
        );
    }

    public function testShouldReturnInventoryProfitability(): void
    {
        $calculateInventoryProfitability = $this->createMock(CalculateInventoryProfitabilityUseCase::class);
        $calculateInventoryProfitability
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn([
                'coins' => [],
                'summary' => [
                    'byCurrency' => [],
                ],
            ]);

        $tools = new CoinInventoryTools(
            findAllCoins: $this->unusedFindAllCoinsUseCase(),
            placeCoin: $this->unusedPlaceCoinUseCase(),
            calculateInventoryProfitability: $calculateInventoryProfitability,
            validator: $this->validationService(),
            constraintsBuilder: new ConstraintsBuilder(),
            coinInputValidator: new CoinInputValidator()
        );

        $result = $tools->profitability();

        $this->assertMcpStructuredContentRecord($result);
        $this->assertSame(
            [
                'coins' => [],
                'summary' => [
                    'byCurrency' => [],
                ],
            ],
            $result
        );
    }

    private function coin(): Coin
    {
        return new Coin(
            'id',
            'name',
            'description',
            '1000',
            'EUR',
            Metal::Gold->value,
            10,
            99.999,
            25,
            Country::USA->value,
            2015,
            '10',
            24,
            25.1,
            '2023-12-01T00:15:00+02:00'
        );
    }

    /**
     * @return array<string, float|int|string>
     */
    private function coinData(): array
    {
        return [
            'country' => 'usa',
            'description' => 'description',
            'id' => 'id',
            'karats' => 24,
            'marketMetalPriceValue' => '10',
            'metal' => 'gold',
            'name' => 'name',
            'nominal' => 25,
            'purchaseCurrency' => 'EUR',
            'purchaseDate' => '2023-12-01T00:15:00+02:00',
            'purchasePrice' => '1000',
            'pureMetalWeight' => 25.1,
            'purity' => 99.999,
            'weight' => 10.0,
            'year' => 2015,
        ];
    }

    private function unusedFindAllCoinsUseCase(): FindAllCoinsUseCase
    {
        $useCase = $this->createMock(FindAllCoinsUseCase::class);
        $useCase
            ->expects($this->never())
            ->method('__invoke');

        return $useCase;
    }

    private function unusedPlaceCoinUseCase(): PlaceCoinUseCase
    {
        $useCase = $this->createMock(PlaceCoinUseCase::class);
        $useCase
            ->expects($this->never())
            ->method('__invoke');

        return $useCase;
    }

    private function unusedCalculateInventoryProfitabilityUseCase(): CalculateInventoryProfitabilityUseCase
    {
        $useCase = $this->createMock(CalculateInventoryProfitabilityUseCase::class);
        $useCase
            ->expects($this->never())
            ->method('__invoke');

        return $useCase;
    }

    private function validationService(): ValidationService
    {
        return new ValidationService(Validation::createValidator());
    }

    /**
     * MCP clients validate structuredContent as a JSON object, not a top-level array.
     */
    private function assertMcpStructuredContentRecord(array $result): void
    {
        $this->assertFalse(array_is_list($result));
    }
}
