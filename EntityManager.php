<?php

namespace Codememory\Components\Database\Orm;

use Codememory\Components\Database\Connection\Interfaces\ConnectorInterface;
use Codememory\Components\Database\Orm\Exceptions\ObjectIsNotEntityException;
use Codememory\Components\Database\Orm\Exceptions\RepositoryEntityNotExistsException;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityManagerInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityRepositoryInterface;
use Codememory\Components\Database\Orm\QueryBuilder\ExtendedQueryBuilder;
use Codememory\Components\Database\Orm\Repository\DefaultRepository;
use JetBrains\PhpStorm\Pure;
use ReflectionException;
use Codememory\Components\Database\Orm\Repository\AbstractEntityRepository;

/**
 * Class EntityManager
 *
 * @package Codememory\Components\Database\Orm
 *
 * @author  Codememory
 */
class EntityManager implements EntityManagerInterface
{

    /**
     * @var ConnectorInterface
     */
    private ConnectorInterface $connector;

    /**
     * @var array
     */
    private array $commits = [];

    /**
     * EntityManager constructor.
     *
     * @param ConnectorInterface $connector
     */
    #[Pure]
    public function __construct(ConnectorInterface $connector)
    {

        $this->connector = $connector;

    }

    /**
     * @inheritDoc
     * @throws ObjectIsNotEntityException
     * @throws ReflectionException
     */
    public function commit(object $entity): EntityManagerInterface
    {

        if ($this->isEntity($entity)) {
            $this->commits[] = clone $entity;
        }

        return $this;

    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function flush(): EntityManagerInterface
    {

        $flush = new Flush($this->connector, $this->commits);

        $flush->flush();
        $this->commits = [];

        return $this;

    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function getRepository(string|object $entity, ?ExtendedQueryBuilder $queryBuilder = null): AbstractEntityRepository
    {

        $entityToObject = is_string($entity) ? new $entity() : $entity;
        $entityData = $this->getEntityData($entityToObject);
        $queryBuilder = $queryBuilder ?: new ExtendedQueryBuilder($this->connector, $entityToObject, $entityData);

        if ($this->getEntityData($entity)->existRepository()) {
            $namespaceRepository = $entityData->getNamespaceRepository();

            return new $namespaceRepository($this, $queryBuilder, $entityData);
        }

        return new DefaultRepository($this, $queryBuilder, $entityData);

    }

    /**
     * @inheritDoc
     * @throws ObjectIsNotEntityException
     * @throws ReflectionException
     */
    public function isEntity(object|string $entity): bool
    {

        if (!$this->getEntityData($entity)->isEntity()) {
            throw new ObjectIsNotEntityException($entity);
        }

        return true;

    }

    /**
     * @inheritDoc
     * @throws ObjectIsNotEntityException
     * @throws ReflectionException
     * @throws RepositoryEntityNotExistsException
     */
    public function isExistEntityRepository(string|object $entity): bool
    {

        $this->isEntity($entity);

        if (!$this->getEntityData($entity)->existRepository()) {
            throw new RepositoryEntityNotExistsException(is_string($entity) ? $entity : $entity::class);
        }

        return true;

    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function getEntityData(string|object $entity): EntityDataInterface
    {

        return new EntityData($entity);

    }

}