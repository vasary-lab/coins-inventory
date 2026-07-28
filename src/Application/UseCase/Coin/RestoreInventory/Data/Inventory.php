<?php

declare(strict_types=1);

namespace Application\UseCase\Coin\RestoreInventory\Data;

use Domain\Coin\Model\Coin;
use Domain\Common\Enum\Country;
use Domain\Common\Enum\Metal;
use Domain\Common\Service\Money\CharInitializerServiceInterface;
use Domain\Common\Service\Money\DateTimeInitializerServiceInterface;
use Domain\Common\Service\Money\MoneyInitializerServiceInterface;
use Domain\Common\ValueObject\Id;
use Generator;

final readonly class Inventory
{
    public function __construct(
        private CharInitializerServiceInterface $charInitializerService,
        private MoneyInitializerServiceInterface $moneyInitializerService,
        private DateTimeInitializerServiceInterface $dateTimeInitializerService,
    ) {
    }

    /**
     * @return Generator<Coin>
     */
    public function get(): Generator
    {
        yield new Coin(
            id: new Id('d606e4f1-a3e4-4b17-a984-370f2b05a1ab'),
            name: $this->charInitializerService->create('Britannia 2025'),
            description: $this->charInitializerService->create('Investment gold coin from United Kingdom'),
            purchasePrice: $this->moneyInitializerService->create(276500, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 100,
            country: Country::UnitedKingdom,
            year: 2025,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-16T14:29:56+02:00'),
        );

        yield new Coin(
            id: new Id('b4c8ebe7-52c3-42e7-b9b3-986023cb47e0'),
            name: $this->charInitializerService->create('Britannia 2024'),
            description: $this->charInitializerService->create('Investment gold coin from United Kingdom'),
            purchasePrice: $this->moneyInitializerService->create(271300, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 100,
            country: Country::UnitedKingdom,
            year: 2024,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-09T12:05:32+02:00'),
        );

        yield new Coin(
            id: new Id('705bc868-97c9-463f-ac2f-5af53edc8edc'),
            name: $this->charInitializerService->create('Britannia 2025'),
            description: $this->charInitializerService->create('Investment gold coin from United Kingdom'),
            purchasePrice: $this->moneyInitializerService->create(276500, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 100,
            country: Country::UnitedKingdom,
            year: 2025,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-16T14:29:56+02:00'),
        );

        yield new Coin(
            id: new Id('62e15c13-f8c4-4cea-a22a-f02fadaca84f'),
            name: $this->charInitializerService->create('Buffalo 2013'),
            description: $this->charInitializerService->create('Investment gold coin from USA'),
            purchasePrice: $this->moneyInitializerService->create(290000, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 50,
            country: Country::USA,
            year: 2013,
            purchaseDate: $this->dateTimeInitializerService->create('2025-02-13T12:15:00+02:00'),
        );

        yield new Coin(
            id: new Id('64d00a52-2f38-4fc1-b6d6-ffd1ff414488'),
            name: $this->charInitializerService->create('Buffalo 2025'),
            description: $this->charInitializerService->create('Investment gold coin from USA'),
            purchasePrice: $this->moneyInitializerService->create(284200, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 50,
            country: Country::USA,
            year: 2025,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-30T13:47:49+02:00'),
        );

        yield new Coin(
            id: new Id('9b4e1170-b626-4854-a003-1fb5d6f51309'),
            name: $this->charInitializerService->create('American Eagle 2023'),
            description: $this->charInitializerService->create('Investment gold coin from USA'),
            purchasePrice: $this->moneyInitializerService->create(290000, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 50,
            country: Country::USA,
            year: 2023,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-30T16:32:57+02:00'),
        );

        yield new Coin(
            id: new Id('6681c26e-1141-4160-a47c-31d0af334461'),
            name: $this->charInitializerService->create('American Eagle 2025'),
            description: $this->charInitializerService->create('Investment gold coin from USA'),
            purchasePrice: $this->moneyInitializerService->create(284200, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 50,
            country: Country::USA,
            year: 2025,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-30T16:32:57+02:00'),
        );

        yield new Coin(
            id: new Id('b75dea8f-05ab-48d1-9c53-f058eb8bea10'),
            name: $this->charInitializerService->create('Krugerrand 2025'),
            description: $this->charInitializerService->create('Investment gold coin from SAR'),
            purchasePrice: $this->moneyInitializerService->create(276500, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 1,
            country: Country::SouthAfrica,
            year: 2025,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-16T14:29:56+02:00'),
        );

        yield new Coin(
            id: new Id('168f900a-bcac-45a9-a853-e6a9ecc096c6'),
            name: $this->charInitializerService->create('Australian Kangaroo 2023'),
            description: $this->charInitializerService->create('Investment gold coin from Australia'),
            purchasePrice: $this->moneyInitializerService->create(275500, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 100,
            country: Country::Australia,
            year: 2023,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-16T14:29:56+02:00'),
        );

        yield new Coin(
            id: new Id('97c39fc8-4a02-497d-be2f-33ad8b6ed641'),
            name: $this->charInitializerService->create('Krugerrand 2025'),
            description: $this->charInitializerService->create('Investment gold coin from SAR'),
            purchasePrice: $this->moneyInitializerService->create(276500, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 1,
            country: Country::SouthAfrica,
            year: 2025,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-16T14:29:56+02:00'),
        );

        yield new Coin(
            id: new Id('161eb093-d0d8-441d-b398-3311a4021e6e'),
            name: $this->charInitializerService->create('Australian Kangaroo 2023'),
            description: $this->charInitializerService->create('Investment gold coin from Australia'),
            purchasePrice: $this->moneyInitializerService->create(275500, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 100,
            country: Country::Australia,
            year: 2023,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-16T14:29:56+02:00'),
        );

        yield new Coin(
            id: new Id('0da0c9d7-3f4d-42bd-81fc-2860bdd9ff60'),
            name: $this->charInitializerService->create('Australian Kangaroo 2025'),
            description: $this->charInitializerService->create('Investment gold coin from Australia'),
            purchasePrice: $this->moneyInitializerService->create(291000, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 100,
            country: Country::Australia,
            year: 2025,
            purchaseDate: $this->dateTimeInitializerService->create('2025-02-13T12:22:44+02:00'),
        );

        yield new Coin(
            id: new Id('71e114f8-92e8-4f48-b0a3-4507547f83da'),
            name: $this->charInitializerService->create('American Eagle 1986'),
            description: $this->charInitializerService->create('Investment gold coin from USA'),
            purchasePrice: $this->moneyInitializerService->create(144000, 'EUR'),
            metal: Metal::Gold,
            weight: 15.55175,
            purity: 99.99,
            nominal: 50,
            country: Country::USA,
            year: 1986,
            purchaseDate: $this->dateTimeInitializerService->create('2024-12-12T10:37:28+02:00'),
        );

        yield new Coin(
            id: new Id('c372defb-4a48-40f4-9ceb-a6b5beaef273'),
            name: $this->charInitializerService->create('Maple Leaf 2021'),
            description: $this->charInitializerService->create('Investment gold coin from Canada'),
            purchasePrice: $this->moneyInitializerService->create(289600, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 50,
            country: Country::Canada,
            year: 2021,
            purchaseDate: $this->dateTimeInitializerService->create('2025-01-02T10:32:54+02:00'),
        );

        yield new Coin(
            id: new Id('40d6bc16-839e-4950-ae86-a88c0d0f7339'),
            name: $this->charInitializerService->create('Maple Leaf 2025'),
            description: $this->charInitializerService->create('Investment gold coin from Canada'),
            purchasePrice: $this->moneyInitializerService->create(286900, 'EUR'),
            metal: Metal::Gold,
            weight: 31.1035,
            purity: 99.99,
            nominal: 50,
            country: Country::Canada,
            year: 2025,
            purchaseDate: $this->dateTimeInitializerService->create('2025-02-13T12:14:00+02:00'),
        );

        yield new Coin(
            id: new Id('06aa53c7-ea40-4ee9-a870-eac00c835182'),
            name: $this->charInitializerService->create('Australian Kangaroo 2021'),
            description: $this->charInitializerService->create('Investment gold coin from Australia'),
            purchasePrice: $this->moneyInitializerService->create(66600, 'EUR'),
            metal: Metal::Gold,
            weight: 7.775875,
            purity: 99.99,
            nominal: 25,
            country: Country::Australia,
            year: 2021,
            purchaseDate: $this->dateTimeInitializerService->create('2024-12-06T13:14:00+02:00'),
        );
    }
}
