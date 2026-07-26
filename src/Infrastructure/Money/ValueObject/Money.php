<?php

declare(strict_types=1);

namespace Infrastructure\Money\ValueObject;

use Domain\Common\ValueObject\Money as DomainMoneyInterface;
use Money\Currency;
use Money\Money as ExternalMoney;
use Money\MoneyFormatter;

final readonly class Money implements DomainMoneyInterface
{
    private ExternalMoney $money;

    public function __construct(string|float|int $amount, string $currency, private MoneyFormatter $formatter)
    {
        $this->money = new ExternalMoney($amount, new Currency($currency));
    }

    public function getAmount(): string
    {
        return $this->formatter->format($this->money);
    }

    public function getCurrency(): string
    {
        return $this->money->getCurrency()->getCode();
    }

    public function multiply(float $multiplier): DomainMoneyInterface
    {
        $newMoney = $this->money->multiply((string) $multiplier);

        return new self($newMoney->getAmount(), $newMoney->getCurrency()->getCode(), $this->formatter);
    }
}
