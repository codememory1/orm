<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckConnectionCommand
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
class CheckConnectionCommand extends AbstractCommand
{

    /**
     * @var string|null
     */
    protected ?string $command = 'db:check-connection';

    /**
     * @var string|null
     */
    protected ?string $description = 'Check database connection';

    /**
     * @inheritDoc
     */
    protected function handler(InputInterface $input, OutputInterface $output): int
    {

        if ($this->isConnection()) {
            $this->io->success([
                'Connection to the database is successful'
            ]);

            return Command::SUCCESS;
        }

        $this->io->error('Failed to connect to database. Check the correctness of the input data');

        return Command::FAILURE;

    }

}