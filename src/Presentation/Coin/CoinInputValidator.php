<?php

declare(strict_types=1);

namespace Presentation\Coin;

use DateInterval;
use DateTimeImmutable;
use Infrastructure\Validation\ConstraintsBuilder;
use Symfony\Component\Validator\Constraints\Collection;

final readonly class CoinInputValidator
{
    public function constraints(ConstraintsBuilder $builder): Collection
    {
        $maximalYear = (int) new DateTimeImmutable()->add(new DateInterval('P1Y'))->format('Y');

        return $builder
            ->notBlank('country')
            ->choice('country', 'unitedKingdom', 'usa', 'canada', 'australia')
            ->notBlank('description')
            ->length('description', max: 1000)
            ->notBlank('metal')
            ->choice('metal', 'Gold')
            ->notBlank('name')
            ->length('name', max: 255)
            ->notBlank('nominal')
            ->positiveOrZero('nominal')
            ->notBlank('purchaseCurrency')
            ->currency('purchaseCurrency')
            ->notBlank('purchaseDate')
            ->date('purchaseDate')
            ->notBlank('purchasePrice')
            ->positive('purchasePrice')
            ->notBlank('purity')
            ->range('purity', min: 91.0, max: 99.999)
            ->notBlank('weight')
            ->positive('weight')
            ->notBlank('year')
            ->range('year', min: 1900, max: $maximalYear)
            ->build();
    }
}
