<?php

namespace Codememory\Components\Database\Orm\Repository;

use Codememory\Components\Database\Orm\EntityData;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityManagerInterface;
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

    use BasicBuildersTrait;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

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
     * @param EntityManagerInterface $entityManager
     * @param ExtendedQueryBuilder   $queryBuilder
     * @param EntityDataInterface    $entityData
     */
    public function __construct(EntityManagerInterface $entityManager, ExtendedQueryBuilder $queryBuilder, EntityDataInterface $entityData)
    {

        $this->entityManager = $entityManager;
        $this->queryBuilder = $queryBuilder;
        $this->entityData = $entityData;

    }

    /**
     * @inheritDoc
     * @return Generator
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function findAll(): Generator
    {

        $qb = $this->createQueryBuilder();

        $qb->select()->from($this->getEntityData()->getTableName());

        $qbClone = clone $qb;

        return $qb->generateQuery()->generator($qbClone->generateQuery()->getResult()->toArray());

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
     * @param string|object|null $entity
     *
     * @return EntityDataInterface
     * @throws ReflectionException
     */
    protected function getEntityData(string|object|null $entity = null): EntityDataInterface
    {

        if (null !== $entity) {
            return new EntityData($entity);
        }

        return $this->entityData;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns the repository of the entity
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     * 
     * @param string $entity
     *
     * @return EntityRepositoryInterface
     */
    protected function getRepository(string $entity): EntityRepositoryInterface
    {

        return $this->entityManager->getRepository($entity);

    }

}