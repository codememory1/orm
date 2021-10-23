<?php

namespace Codememory\Components\Database\Orm\Repository;

use Codememory\Components\Database\Orm\EntityData;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityManagerInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityRepositoryInterface;
use Codememory\Components\Database\Orm\QueryBuilder\ExtendedQueryBuilder;
use Codememory\Components\Database\Orm\Repository\BasicQueries\CounterTrait;
use Codememory\Components\Database\Orm\Repository\BasicQueries\ExtremumTrait;
use Codememory\Components\Database\Orm\Repository\BasicQueries\FindTrait;
use JetBrains\PhpStorm\Pure;
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

    use FindTrait;
    use ExtremumTrait;
    use CounterTrait;

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
     * @var ConstructionAssistant
     */
    private ConstructionAssistant $constructionAssistant;

    /**
     * AbstractEntityRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ExtendedQueryBuilder   $queryBuilder
     * @param EntityDataInterface    $entityData
     */
    #[Pure]
    public function __construct(EntityManagerInterface $entityManager, ExtendedQueryBuilder $queryBuilder, EntityDataInterface $entityData)
    {

        $this->entityManager = $entityManager;
        $this->queryBuilder = $queryBuilder;
        $this->entityData = $entityData;

        $this->constructionAssistant = new ConstructionAssistant();

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
     * @param string                    $entity
     * @param ExtendedQueryBuilder|null $queryBuilder
     *
     * @return AbstractEntityRepository
     */
    protected function getRepository(string $entity, ?ExtendedQueryBuilder $queryBuilder = null): AbstractEntityRepository
    {

        return $this->entityManager->getRepository($entity, $queryBuilder);

    }

}