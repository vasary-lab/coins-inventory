<?php

declare(strict_types=1);

namespace Presentation\API\HTTP\Coin;

use Application\UseCase\Coin\Place\PlaceCoinUseCase;
use Application\UseCase\Coin\Place\Request\PlaceCoinRequest;
use Infrastructure\Framework\Symfony\HTTP\Request\CoinRequest;
use Infrastructure\Framework\Symfony\Routing\Controller;
use Infrastructure\Framework\Symfony\Routing\JsonResponse;
use Infrastructure\Framework\Symfony\Routing\StatusCodeInterface;
use Infrastructure\Validation\ConstraintsBuilder;
use Infrastructure\Validation\ValidationService;
use Presentation\Coin\CoinInputValidator;

final class PlaceCoinController extends Controller
{
    public function __invoke(
        PlaceCoinUseCase $useCase,
        CoinRequest $coinRequest,
        ValidationService $validator,
        ConstraintsBuilder $constraintsBuilder,
        CoinInputValidator $coinInputValidator,
    ): JsonResponse {
        $data = json_decode($coinRequest->getContent(), true);
        $violations = $validator->validate($data, $coinInputValidator->constraints($constraintsBuilder));

        if (null !== $violations) {
            return new JsonResponse(iterator_to_array($violations), StatusCodeInterface::BAD_REQUEST);
        }

        $coin = $useCase(
            new PlaceCoinRequest(
                name: $data['name'],
                description: $data['description'],
                purchasePrice: (int)$data['purchasePrice'],
                purchaseCurrency: $data['purchaseCurrency'],
                metal: $data['metal'],
                weight: (float)$data['weight'],
                purity: (float)$data['purity'],
                nominal: (int)$data['nominal'],
                country: $data['country'],
                year: (int)$data['year'],
                purchaseDate: $data['purchaseDate']
            )
        );

        return new JsonResponse($coin, StatusCodeInterface::CREATED);
    }
}
