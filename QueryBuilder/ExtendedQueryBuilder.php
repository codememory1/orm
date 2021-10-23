<?php

namespace Codememory\Components\Database\Orm\QueryBuilder;

use Codememory\Components\Database\Connection\Interfaces\ConnectorInterface;
use Codememory\Components\Database\Orm\Exceptions\ResultNotGeneratedException;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Orm\Interfaces\ExtendedQueryBuilderInterface;
use Codememory\Components\Database\Orm\QueryBuilder\Answer\ResultTo;
use Codememory\Components\Database\QueryBuilder\Exceptions\StatementNotSelectedException;
use Codememory\Components\Database\QueryBuilder\QueryBuilder;
use PDO;

/**
 * Class ExtendedQueryBuilder
 *
 * @package Codememory\Components\Database\Orm\QueryBuilder
 *
 * @author  Codememory
 */
class ExtendedQueryBuilder extends QueryBuilder implements ExtendedQueryBuilderInterface
{

    /**
     * @var object
     */
    private object $entity;

    /**
     * @var EntityDataInterface
     */
    private EntityDataInterface $entityData;

    /**
     * @var bool|array
     */
    private bool|array $records = false;

    /**
     * ExtendedQueryBuilder constructor.
     *
     * @param ConnectorInterface  $connector
     * @param object              $entity
     * @param EntityDataInterface $entityData
     */
    public function __construct(ConnectorInterface $connector, object $entity, EntityDataInterface $entityData)
    {

        parent::__construct($connector, $entityData->getNamespaceRepository() ?: '');

        $this->entity = $entity;
        $this->entityData = $entityData;

    }

    /**
     * @inheritDoc
     */
    public function getEntity(): object
    {

        return $this->entity;

    }

    /**
     * @inheritDoc
     */
    public function getEntityData(): EntityDataInterface
    {

        return $this->entityData;

    }

    /**
     * @inheritDoc
     * @throws StatementNotSelectedException
     */
    public function generateResult(): ExtendedQueryBuilderInterface
    {

        $records = $this->getExecutor()->execute(
            $this->getStatement()->getQuery(),
            $this->getParameters()
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->records = $records;

        return $this;

    }

    /**
     * @inheritDoc
     * @throws ResultNotGeneratedException
     */
    public function to(): ResultTo
    {

        if (false === $this->records) {
            throw new ResultNotGeneratedException();
        }

        return new ResultTo($this, $this->records);

    }

    /**
     * @inheritDoc
     * @throws StatementNotSelectedException
     */
    public function generateTo(): ResultTo
    {

        return $this->generateResult()->to();

    }

    /**
     * @inheritDoc
     */
    public function getGeneratedResult(): bool|array
    {

        return $this->records;

    }

}