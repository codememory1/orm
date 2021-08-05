<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Console\Command;
use Codememory\Components\Database\Orm\EntityData;
use Codememory\Components\Database\Orm\Exceptions\ObjectIsNotEntityException;
use Codememory\Components\Database\Orm\SQLBuilder\DropTableOfEntity;
use Codememory\Components\Database\Orm\Utils;
use ReflectionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DropTableCommand
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
class DropTableCommand extends AbstractCommand
{

    /**
     * @inheritdoc
     */
    protected ?string $command = 'db:drop-table';

    /**
     * @inheritdoc
     */
    protected ?string $description = 'Delete table by entity name';

    /**
     * @inheritdoc
     */
    protected function wrapArgsAndOptions(): Command
    {

        $this->addArgument(
            'entity-name',
            InputArgument::REQUIRED,
            sprintf('Entity name without the %s suffix', $this->tags->yellowText('Entity'))
        );

        return $this;

    }

    /**
     * @inheritDoc
     * @throws ObjectIsNotEntityException
     * @throws ReflectionException
     */
    protected function handler(InputInterface $input, OutputInterface $output): int
    {

        if (false === $this->isConnection($this->connector)) {
            $this->checkConnection();

            return Command::FAILURE;
        }

        $utils = new Utils();
        $entityName = $input->getArgument('entity-name') . $utils->getEntitySuffix();
        $entityNamespace = $utils->getEntityNamespace() . $entityName;
        $entityData = new EntityData($entityNamespace);
        $builderDropTable = new DropTableOfEntity($this->connector, $entityData->getEntity());

        $this->connector->getConnection()->exec($builderDropTable->buildToString());

        $this->io->success([
            sprintf('The %s table was successfully deleted', $entityData->getTableName())
        ]);

        return Command::SUCCESS;

    }

}