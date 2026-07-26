<?php

declare(strict_types=1);

namespace Presentation\API\HTTP\Coin;

use Application\UseCase\Coin\Profitability\CalculateInventoryProfitabilityUseCase;
use Infrastructure\Framework\Symfony\Routing\JsonResponse;
use Infrastructure\Test\TestCase;

final class InventoryProfitabilityControllerTest extends TestCase
{
    public function testShouldSuccessfullyReturnInventoryProfitability(): void
    {
        /** @var InventoryProfitabilityController $controller */
        $controller = $this->getContainer()->get(InventoryProfitabilityController::class);

        $useCase = $this->createMock(CalculateInventoryProfitabilityUseCase::class);
        $useCase
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn([
                'coins' => [],
                'summary' => [
                    'byCurrency' => [],
                ],
            ]);

        $response = $controller->__invoke($useCase);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"coins":[],"summary":{"byCurrency":[]}}',
            $response->getContent()
        );
    }
}
