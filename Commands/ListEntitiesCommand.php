<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Console\Command;
use Codememory\Components\Database\Orm\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListEntitiesCommand
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
class ListEntitiesCommand extends Command
{

    /**
     * @inheritDoc
     */
    protected ?string $command = 'db:list-entities';

    /**
     * @inheritDoc
     */
    protected ?string $description = 'Get a list of all entities';

    /**
     * @inheritDoc
     */
    protected function handler(InputInterface $input, OutputInterface $output): int
    {

        $utils = new Utils();
        $listing = new Listing($utils, $utils->getPathWithEntities(), $utils->getEntitySuffix());

        $this->io->table(
            ['name', 'full name', 'path'],
            $listing->getList()
        );

        return Command::SUCCESS;

    }

}