<?php

namespace Codememory\Components\Database\Orm\QueryBuilder;

use ArrayIterator;
use Codememory\Components\Database\Connection\Interfaces\ConnectorInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Orm\Interfaces\ExtendedQueryBuilderInterface;
use Codememory\Components\Database\QueryBuilder\Exceptions\NotSelectedStatementException;
use Codememory\Components\Database\QueryBuilder\Exceptions\QueryNotGeneratedException;
use Codememory\Components\Database\QueryBuilder\QueryBuilder;
use Generator;
use ReflectionException;

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
     * ExtendedQueryBuilder constructor.
     *
     * @param ConnectorInterface  $connector
     * @param object              $entity
     * @param EntityDataInterface $entityData
     */
    public function __construct(ConnectorInterface $connector, object $entity, EntityDataInterface $entityData)
    {

        parent::__construct($connector);

        $this->entity = $entity;
        $this->entityData = $entityData;

    }

    /**
     * @inheritDoc
     * @throws NotSelectedStatementException
     */
    public function generateQuery(): static
    {

        return parent::generateQuery();

    }

    /**
     * @inheritDoc
     * @return array
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function getResultAsEntity(): array
    {

        $records = $this->getResult()->toArray();
        $resultAsEntity = new ResultAsEntity($this->entity, $this->entityData, $records);

        return $resultAsEntity->getResult();

    }

    /**
     * @inheritDoc
     */
    public function iterator(array $records): ArrayIterator
    {

        return new ArrayIterator($records);

    }

    /**
     * @inheritDoc
     */
    public function generator(array $records): Generator
    {

        foreach ($records as $entity) {
            yield $entity;
        }

    }

}