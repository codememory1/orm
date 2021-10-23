<?php

namespace Codememory\Components\Database\Orm\QueryBuilder\Answer;

use Codememory\Components\Database\Orm\EntityData;
use Codememory\Components\Database\Orm\Interfaces\ResultInterface;
use Codememory\Components\Database\Orm\QueryBuilder\ExtendedQueryBuilder;
use Codememory\Components\Database\Orm\QueryBuilder\ResultAsEntity;
use JetBrains\PhpStorm\Pure;
use ReflectionException;

/**
 * Class ResultTo
 *
 * @package Codememory\Components\Database\Orm\QueryBuilder
 *
 * @author  Codememory
 */
class ResultTo
{

    /**
     * @var ExtendedQueryBuilder
     */
    private ExtendedQueryBuilder $queryBuilder;

    /**
     * @var array
     */
    private array $records;

    /**
     * @param ExtendedQueryBuilder $queryBuilder
     * @param array                $records
     */
    public function __construct(ExtendedQueryBuilder $queryBuilder, array $records)
    {

        $this->queryBuilder = $queryBuilder;
        $this->records = $records;

    }

    /**
     * @return ResultInterface
     */
    #[Pure]
    public function array(): ResultInterface
    {

        return new Result($this->records);

    }

    /**
     * @return ResultInterface
     */
    public function object(): ResultInterface
    {

        return new Result(array_map(function (array $record) {
            return (object) $record;
        }, $this->records));

    }

    /**
     * @param object|null $entity
     * @param bool|array  $records
     *
     * @return ResultInterface
     * @throws ReflectionException
     */
    public function entity(?object $entity = null, bool|array $records = false): ResultInterface
    {

        $entity = $entity ?: $this->queryBuilder->getEntity();
        $entityData = null !== $entity ? new EntityData($entity) : $this->queryBuilder->getEntityData();
        $records = false === $records ? $this->array()->all() : $records;

        $resultToEntity = (new ResultAsEntity($entity, $entityData, $records))->getResult();

        return new Result($resultToEntity);

    }


}