<?php

namespace Codememory\Components\Database\Orm\Repository;

use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityRepositoryInterface;
use Codememory\Components\Database\Orm\QueryBuilder\ExtendedQueryBuilder;
use Codememory\Components\Database\QueryBuilder\Exceptions\NotSelectedStatementException;
use Codememory\Components\Database\QueryBuilder\Exceptions\QueryNotGeneratedException;
use Generator;
use ReflectionException;

/**
 * Class AbstractEntityRepository
 *
 * @package Codememory\Components\Database\Orm\Repository
 *
 * @author  Codememory
 */
abstract class AbstractEntityRepository implements EntityRepositoryInterface
{

    /**
     * @var ExtendedQueryBuilder
     */
    private ExtendedQueryBuilder $queryBuilder;

    /**
     * @var EntityDataInterface
     */
    private EntityDataInterface $entityData;

    /**
     * AbstractEntityRepository constructor.
     *
     * @param ExtendedQueryBuilder $queryBuilder
     * @param EntityDataInterface  $entityData
     */
    public function __construct(ExtendedQueryBuilder $queryBuilder, EntityDataInterface $entityData)
    {

        $this->queryBuilder = $queryBuilder;
        $this->entityData = $entityData;

    }

    /**
     * @inheritDoc
     * @return array
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function findAll(): Generator
    {

        $qb = $this->createQueryBuilder();
        $qb
            ->select()
            ->from($this->getEntityData()->getTableName());

        return $qb->generateQuery()->generator($qb->getResultAsEntity());

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns a clone of the query builder. Should be called before building a query
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return ExtendedQueryBuilder
     */
    protected function createQueryBuilder(): ExtendedQueryBuilder
    {

        return clone $this->queryBuilder;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns the data object of the current entity
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return EntityDataInterface
     */
    protected function getEntityData(): EntityDataInterface
    {

        return $this->entityData;

    }

}