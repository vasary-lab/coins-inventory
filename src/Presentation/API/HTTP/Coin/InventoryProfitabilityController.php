<?php

declare(strict_types=1);

namespace Presentation\API\HTTP\Coin;

use Application\UseCase\Coin\Profitability\CalculateInventoryProfitabilityUseCase;
use Infrastructure\Framework\Symfony\Routing\Controller;
use Infrastructure\Framework\Symfony\Routing\JsonResponse;
use Infrastructure\Framework\Symfony\Routing\StatusCodeInterface;

final class InventoryProfitabilityController extends Controller
{
    public function __invoke(CalculateInventoryProfitabilityUseCase $useCase): JsonResponse
    {
        return new JsonResponse($useCase(), StatusCodeInterface::STATUS_OK);
    }
}
