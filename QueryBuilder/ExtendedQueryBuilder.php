<?php

namespace Codememory\Components\Database\Orm\QueryBuilder;

use ArrayIterator;
use Codememory\Components\Database\Connection\Interfaces\ConnectorInterface;
use Codememory\Components\Database\Orm\EntityData;
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
    public function toEntity(?object $entity = null, bool|array $records = false): array
    {

        $entity = $entity ?: $this->entity;
        $entityData = null !== $entity ? new EntityData($entity) : $this->entityData;
        $records = false !== $records ? $this->getResult()->toArray() : $records;

        return (new ResultAsEntity($entity, $entityData, $records))->getResult();

    }

    /**
     * @inheritDoc
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     */
    public function toArray(): array
    {

        return $this->generateQuery()->getResult()->toArray();

    }

    /**
     * @inheritDoc
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     */
    public function toObject(): array
    {

        return $this->generateQuery()->getResult()->toObject();

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