<?php

namespace Codememory\Components\Database\Orm\Repository\BasicQueries;

use Codememory\Components\Database\QueryBuilder\Exceptions\StatementNotSelectedException;
use Codememory\Support\Str;
use ReflectionException;

/**
 * Trait ExtremumTrait
 *
 * @package Codememory\Components\Database\Orm\Repository\BasicQueries
 *
 * @author  Codememory
 */
trait ExtremumTrait
{

    /**
     * @param string $func
     * @param string $column
     *
     * @return int
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function getExtremum(string $func, string $column): int
    {

        $qb = $this->createQueryBuilder();
        $fullFunction = sprintf('%s(%s)', Str::toUppercase($func), $column);

        $qb->select(['count' => $fullFunction])->from($this->getEntityData()->getTableName());

        return $qb->generateTo()->array()->first()['count'];

    }

    /**
     * @param string $column
     *
     * @return int
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function getMin(string $column): int
    {

        return $this->getExtremum('max', $column);

    }

    /**
     * @param string $column
     *
     * @return int
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function getMax(string $column): int
    {

        return $this->getExtremum('max', $column);

    }

    /**
     * @return int
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function getMinId(): int
    {

        return $this->getMin('id');

    }

    /**
     * @return int
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function getMaxId(): int
    {

        return $this->getMax('id');

    }

}