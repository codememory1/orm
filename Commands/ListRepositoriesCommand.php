<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Console\Command;
use Codememory\Components\Database\Orm\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListRepositoriesCommand
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
class ListRepositoriesCommand extends Command
{

    /**
     * @inheritDoc
     */
    protected ?string $command = 'db:list-repositories';

    /**
     * @inheritDoc
     */
    protected ?string $description = 'Get a list of all repositories';

    /**
     * @inheritDoc
     */
    protected function handler(InputInterface $input, OutputInterface $output): int
    {

        $utils = new Utils();
        $listing = new Listing($utils, $utils->getPathWithRepositories(), $utils->getRepositorySuffix());

        $this->io->table(
            ['name', 'full name', 'path'],
            $listing->getList()
        );

        return Command::SUCCESS;

    }

}