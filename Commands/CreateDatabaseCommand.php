<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Console\Command;
use Codememory\Components\Database\Connection\ConnectorConfiguration;
use Codememory\Components\Database\Schema\Statements\Administration\Show\Databases;
use Codememory\Components\Database\Schema\Statements\Definition\CreateDatabase;
use PDO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateDatabaseCommand
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
class CreateDatabaseCommand extends AbstractCommand
{

    /**
     * @var string|null
     */
    protected ?string $command = 'db:create-db';

    /**
     * @var string|null
     */
    protected ?string $description = 'Create database of current connection if it doesn\'t exist';

    /**
     * @inheritDoc
     */
    protected function handler(InputInterface $input, OutputInterface $output): int
    {

        $createDatabase = new CreateDatabase();
        $showDatabases = new Databases();

        $connector = $this->connection->reconnect($this->connector->getConnectorName(), function (ConnectorConfiguration $configuration) {
            $configuration->dbname();
        });

        if(false === $this->isConnection($connector)) {
            $this->checkConnection($connector);

            return Command::FAILURE;
        }

        $connect = $connector->getConnection();
        $databaseNameToCreated = $this->connector->getConnectorData()->getDbname();

        $databases = $connect->query($showDatabases->databases()->getQuery())->fetchAll(PDO::FETCH_COLUMN);
        $createDatabaseQuery = $createDatabase
            ->database($databaseNameToCreated)
            ->collate('utf8_general_ci')
            ->getQuery();

        if (in_array($databaseNameToCreated, $databases)) {
            $this->io->warning([
                sprintf('The %s database has already been created', $databaseNameToCreated)
            ]);

            return Command::INVALID;
        }

        $connect->exec($createDatabaseQuery);

        $this->io->success([
            sprintf('Database %s created successfully', $databaseNameToCreated)
        ]);

        return Command::SUCCESS;

    }

}