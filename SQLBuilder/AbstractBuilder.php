<?php

namespace Codememory\Components\Database\Orm\SQLBuilder;

use Codememory\Components\Database\Connection\Interfaces\ConnectorInterface;
use Codememory\Components\Database\Orm\EntityData;
use Codememory\Components\Database\Orm\Exceptions\ObjectIsNotEntityException;
use ReflectionException;

/**
 * Class AbstractBuilder
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder
 *
 * @author  Codememory
 */
abstract class AbstractBuilder
{

    /**
     * @var ConnectorInterface
     */
    protected ConnectorInterface $connector;

    /**
     * @var object
     */
    protected object $entity;

    /**
     * @var EntityData
     */
    protected EntityData $entityData;

    /**
     * @var AlterHelper
     */
    protected AlterHelper $alterHelper;

    /**
     * AbstractBuilder constructor.
     *
     * @param ConnectorInterface $connector
     * @param object|string      $entity
     *
     * @throws ObjectIsNotEntityException
     * @throws ReflectionException
     */
    public function __construct(ConnectorInterface $connector, object|string $entity)
    {

        $this->connector = $connector;
        $this->entity = $entity;
        $this->entityData = new EntityData($this->entity);

        if (!$this->entityData->isEntity()) {
            throw new ObjectIsNotEntityException($entity);
        }

        $this->alterHelper = new AlterHelper($this->entityData);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Build queries and return all queries in an array
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return array
     */
    abstract public function buildToArray(): array;

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Build queries and return all queries in the string
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return string
     */
    abstract public function buildToString(): string;

}