<?php

declare(strict_types=1);

namespace Presentation\Console\RestoreInventory;

use Application\UseCase\Coin\RestoreInventory\RestoreInventoryUseCase;
use Infrastructure\Framework\Symfony\Command\AbstractCoinInventory;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'coin-inventory:restore',
    description: 'Clears coin inventory and sets prepared collection'
)]
final class SetInventoryCommand extends AbstractCoinInventory
{
    public function __construct(private readonly RestoreInventoryUseCase $restoreInventoryUseCase)
    {
        parent::__construct();
    }

    protected function name(): string
    {
        return 'coin-inventory:restore';
    }

    protected function description(): string
    {
        return 'Clears coin inventory and sets prepared collection';
    }

    protected function handle(): int
    {
        $this->restoreInventoryUseCase->execute();

        return self::EXIT_SUCCESS;
    }
}
