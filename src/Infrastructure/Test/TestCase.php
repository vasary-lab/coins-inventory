<?php

declare(strict_types=1);

namespace Infrastructure\Test;

use Infrastructure\Persistence\Redis\Repository\CoinRepository;
use Infrastructure\Test\Fixture\CoinFixture;
use Predis\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get(ClientInterface::class)->flushAll();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->getContainer()->get(ClientInterface::class)->flushAll();

        restore_exception_handler();
    }

    protected function loadCoinsFixture(): void
    {
        /** @var CoinRepository $repository */
        $repository = $this->getContainer()->get(CoinRepository::class);

        /** @var CoinFixture $fixtures */
        $fixtures = $this->getContainer()->get(CoinFixture::class);

        foreach ($fixtures->items() as $fixture) {
            $repository->add($fixture);
        }
    }
}
