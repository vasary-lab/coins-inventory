<?php

declare(strict_types=1);

namespace Presentation\API\HTTP\Coin;

use Application\Service\Serializer\DTO\Coin;
use Application\UseCase\Coin\Place\PlaceCoinUseCase;
use DateInterval;
use DateTime;
use Domain\Common\Enum\Country;
use Domain\Common\Enum\Metal;
use Infrastructure\Framework\Symfony\HTTP\Request\CoinRequest;
use Infrastructure\Framework\Symfony\Routing\JsonResponse;
use Infrastructure\Test\TestCase;
use Infrastructure\Validation\ConstraintsBuilder;
use Infrastructure\Validation\ValidationService;
use PHPUnit\Framework\Attributes\DataProvider;

final class PlaceCoinControllerTest extends TestCase
{
    #[DataProvider('validationDataProvider')]
    public function testShouldHandleInvalidValidationRules(array $requestData, array $expectedViolations): void
    {
        /** @var PlaceCoinController $controller */
        $controller = $this->getContainer()->get(PlaceCoinController::class);

        /** @var ValidationService $validator */
        $validator = $this->getContainer()->get(ValidationService::class);

        /** @var CoinRequest $coinRequest */
        $coinRequest = $this->createMock(CoinRequest::class);
        $coinRequest
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($requestData));

        $useCase = $this->createMock(PlaceCoinUseCase::class);
        $useCase
            ->expects($this->never())
            ->method('__invoke');

        $response = $controller->__invoke(
            useCase: $useCase,
            coinRequest: $coinRequest,
            validator: $validator,
            constraintsBuilder: new ConstraintsBuilder()
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $content = $response->getContent();

        $this->assertNotNull($expectedViolations);
        $this->assertEquals(json_encode($expectedViolations), $content);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testShouldSuccessfullyCallUseCase(): void
    {
        /** @var PlaceCoinController $controller */
        $controller = $this->getContainer()->get(PlaceCoinController::class);

        /** @var ValidationService $validator */
        $validator = $this->getContainer()->get(ValidationService::class);

        /** @var CoinRequest $coinRequest */
        $coinRequest = $this->createMock(CoinRequest::class);
        $coinRequest
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode([
                'country' => 'usa',
                'description' => 'description',
                'metal' => 'Gold',
                'name' => 'Gold Coin',
                'nominal' => 100,
                'purchaseCurrency' => 'USD',
                'purchaseDate' => '2023-01-01',
                'purchasePrice' => 1500,
                'purity' => 99.9,
                'weight' => 1.0,
                'year' => 2020,
            ]));

        $useCase = $this->createMock(PlaceCoinUseCase::class);
        $useCase
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(new Coin(
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
            ))
        ;

        $response = $controller->__invoke(
            useCase: $useCase,
            coinRequest: $coinRequest,
            validator: $validator,
            constraintsBuilder: new ConstraintsBuilder()
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $content = $response->getContent();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/response/place_a_coin.json', $content);
    }

    public static function validationDataProvider(): array
    {
        $maximalYear = (int) new DateTime()->add(new DateInterval('P1Y'))->format('Y');

        return [
            'invalid country' => [
                [
                    'country' => 'mars',
                    'description' => 'A rare coin.',
                    'metal' => 'Gold',
                    'name' => 'Gold Coin',
                    'nominal' => 100,
                    'purchaseCurrency' => 'USD',
                    'purchaseDate' => '2023-01-01',
                    'purchasePrice' => 1500,
                    'purity' => 99.9,
                    'weight' => 1.0,
                    'year' => 2020,
                ],
                [
                    ['field' => '[country]', 'message' => 'The value you selected is not a valid choice.'],
                ],
            ],
            'invalid description' => [
                [
                    'country' => 'usa',
                    'description' => str_repeat('a', 1001),
                    'metal' => 'Gold',
                    'name' => 'Gold Coin',
                    'nominal' => 100,
                    'purchaseCurrency' => 'USD',
                    'purchaseDate' => '2023-01-01',
                    'purchasePrice' => 1500,
                    'purity' => 99.9,
                    'weight' => 1.0,
                    'year' => 2020,
                ],
                [
                    [
                        'field' => '[description]',
                        'message' => 'This value is too long. It should have 1000 characters or less.',
                    ],
                ],
            ],
            'invalid metal' => [
                [
                    'country' => 'usa',
                    'description' => 'A rare coin.',
                    'metal' => 'Wood',
                    'name' => 'Gold Coin',
                    'nominal' => 100,
                    'purchaseCurrency' => 'USD',
                    'purchaseDate' => '2023-01-01',
                    'purchasePrice' => 1500,
                    'purity' => 99.9,
                    'weight' => 1.0,
                    'year' => 2020,
                ],
                [
                    ['field' => '[metal]', 'message' => 'The value you selected is not a valid choice.'],
                ],
            ],
            'invalid name' => [
                [
                    'country' => 'usa',
                    'description' => 'A rare coin.',
                    'metal' => 'Gold',
                    'name' => '',
                    'nominal' => 100,
                    'purchaseCurrency' => 'USD',
                    'purchaseDate' => '2023-01-01',
                    'purchasePrice' => 1500,
                    'purity' => 99.9,
                    'weight' => 1.0,
                    'year' => 2020,
                ],
                [
                    ['field' => '[name]', 'message' => 'This value should not be blank.'],
                ],
            ],
            'invalid purchaseCurrency' => [
                [
                    'country' => 'usa',
                    'description' => 'A rare coin.',
                    'metal' => 'Gold',
                    'name' => 'Gold Coin',
                    'nominal' => 100,
                    'purchaseCurrency' => 'INVALID',
                    'purchaseDate' => '2023-01-01',
                    'purchasePrice' => 1500,
                    'purity' => 99.9,
                    'weight' => 1.0,
                    'year' => 2020,
                ],
                [
                    ['field' => '[purchaseCurrency]', 'message' => 'This value is not a valid currency.'],
                ],
            ],
            'invalid purchaseDate' => [
                [
                    'country' => 'usa',
                    'description' => 'A rare coin.',
                    'metal' => 'Gold',
                    'name' => 'Gold Coin',
                    'nominal' => 100,
                    'purchaseCurrency' => 'USD',
                    'purchaseDate' => 'invalid-date',
                    'purchasePrice' => 1500,
                    'purity' => 99.9,
                    'weight' => 1.0,
                    'year' => 2020,
                ],
                [
                    ['field' => '[purchaseDate]', 'message' => 'This value is not a valid date.'],
                ],
            ],
            'invalid purchasePrice' => [
                [
                    'country' => 'usa',
                    'description' => 'A rare coin.',
                    'metal' => 'Gold',
                    'name' => 'Gold Coin',
                    'nominal' => 100,
                    'purchaseCurrency' => 'USD',
                    'purchaseDate' => '2023-01-01',
                    'purchasePrice' => -1500,
                    'purity' => 99.9,
                    'weight' => 1.0,
                    'year' => 2020,
                ],
                [
                    ['field' => '[purchasePrice]', 'message' => 'This value should be positive.'],
                ],
            ],
            'invalid weight' => [
                [
                    'country' => 'usa',
                    'description' => 'A rare coin.',
                    'metal' => 'Gold',
                    'name' => 'Gold Coin',
                    'nominal' => 100,
                    'purchaseCurrency' => 'USD',
                    'purchaseDate' => '2023-01-01',
                    'purchasePrice' => 1500,
                    'purity' => 99.9,
                    'weight' => -1.0,
                    'year' => 2020,
                ],
                [
                    ['field' => '[weight]', 'message' => 'This value should be positive.'],
                ],
            ],
            'invalid year' => [
                [
                    'country' => 'usa',
                    'description' => 'A rare coin.',
                    'metal' => 'Gold',
                    'name' => 'Gold Coin',
                    'nominal' => 100,
                    'purchaseCurrency' => 'USD',
                    'purchaseDate' => '2023-01-01',
                    'purchasePrice' => 1500,
                    'purity' => 99.9,
                    'weight' => 1.0,
                    'year' => 1899,
                ],
                [
                    ['field' => '[year]', 'message' => 'This value should be between 1900 and ' . $maximalYear . '.'],
                ],
            ],
        ];
    }
}
