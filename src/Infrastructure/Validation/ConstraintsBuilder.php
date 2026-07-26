<?php

declare(strict_types=1);

namespace Infrastructure\Validation;

use Symfony\Component\Validator\Constraints as Assert;

final class ConstraintsBuilder
{
    private array $constraints = [];

    public function notBlank(string $key): self
    {
        $this->constraints[$key][] = new Assert\NotBlank();

        return $this;
    }

    public function length(string $key, int $min = 0, int $max = 0): self
    {
        $this->constraints[$key][] = new Assert\Length(
            min: 0 === $min ? null : $min,
            max: 0 === $max ? null : $max
        );

        return $this;
    }

    public function choice(string $key, string ...$options): self
    {
        $this->constraints[$key][] = new Assert\Choice(choices: $options);

        return $this;
    }

    public function positiveOrZero(string $key): self
    {
        $this->constraints[$key][] = new Assert\PositiveOrZero();

        return $this;
    }

    public function currency(string $key): self
    {
        $this->constraints[$key][] = new Assert\Currency();

        return $this;
    }

    public function positive(string $key): self
    {
        $this->constraints[$key][] = new Assert\Positive();

        return $this;
    }

    public function range(string $key, float|int $min = 0.0, float|int $max = 0.0): self
    {
        $this->constraints[$key][] = new Assert\Range(min: $min, max: $max);

        return $this;
    }

    public function date(string $key): self
    {
        $this->constraints[$key][] = new Assert\Date();

        return $this;
    }

    public function build(): object
    {
        $constraints = $this->constraints;
        $this->constraints = [];

        return new Assert\Collection($constraints);
    }
}
