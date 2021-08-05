<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Caching\Exceptions\ConfigPathNotExistException;
use Codememory\Components\Console\Command;
use Codememory\Components\Database\Orm\EntityCache;
use Codememory\Components\Database\Orm\EntityData;
use Codememory\Components\Database\Orm\Exceptions\ObjectIsNotEntityException;
use Codememory\Components\Database\Orm\SQLBuilder\CreateTableOfEntity;
use Codememory\Components\Database\Orm\Utils;
use ReflectionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateTableCommand
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
class CreateTableCommand extends AbstractCommand
{

    /**
     * @inheritdoc
     */
    protected ?string $command = 'db:create-table';

    /**
     * @inheritdoc
     */
    protected ?string $description = 'Create table from entity';

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
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws ConfigPathNotExistException
     * @throws ReflectionException
     * @throws ObjectIsNotEntityException
     */
    protected function handler(InputInterface $input, OutputInterface $output): int
    {

        if(false === $this->isConnection($this->connector)) {
            $this->checkConnection();

            return Command::FAILURE;
        }

        $utils = new Utils();
        $entityName = $input->getArgument('entity-name').$utils->getEntitySuffix();
        $entityNamespace = $utils->getEntityNamespace().$entityName;
        $entityPath = $utils->getPathWithEntities().$entityName.'.php';
        $entityData = new EntityData($entityNamespace);
        $entityCache = new EntityCache($entityData);
        $builderCreateTable = new CreateTableOfEntity($this->connector, $entityData->getEntity());

        $this->connector->getConnection()->exec($builderCreateTable->buildToString());
        $entityCache->createEntityCache($entityPath);

        $this->io->success([
            sprintf('Table %s created successfully', $entityData->getTableName())
        ]);

        return Command::SUCCESS;

    }

}