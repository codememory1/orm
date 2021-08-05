<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Console\Command;
use Codememory\Components\Database\Connection\Interfaces\ConnectionInterface;
use Codememory\Components\Database\Connection\Interfaces\ConnectorInterface;
use Codememory\Support\Str;
use Exception;
use PDO;

/**
 * Class AbstractCommand
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
abstract class AbstractCommand extends Command
{

    /**
     * @var ConnectorInterface
     */
    protected ConnectorInterface $connector;

    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $connection;

    /**
     * @var string
     */
    protected string $reconnectionName;

    /**
     * AbstractCommand constructor.
     *
     * @param ConnectorInterface  $connector
     * @param ConnectionInterface $connection
     *
     * @throws Exception
     */
    public function __construct(ConnectorInterface $connector, ConnectionInterface $connection)
    {

        parent::__construct();

        $this->connector = $connector;
        $this->connection = $connection;
        $this->reconnectionName = Str::random(25);

    }

    /**
     * @return PDO
     */
    protected function getConnection(): PDO
    {

        return $this->connector->getConnection();

    }

    /**
     * @param ConnectorInterface|null $connector
     *
     * @return bool
     */
    protected function isConnection(?ConnectorInterface $connector = null): bool
    {

        if(null !== $connector) {
            return $connector->isConnection();
        }

        return $this->connector->isConnection();

    }

    /**
     * @param ConnectorInterface|null $connector
     */
    protected function checkConnection(?ConnectorInterface $connector = null): void
    {

        $this->io->error('Failed to connect to database. Check the correctness of the input data');

    }

}