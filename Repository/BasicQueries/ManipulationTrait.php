<?php

namespace Codememory\Components\Database\Orm\Repository\BasicQueries;

use Codememory\Components\Database\QueryBuilder\Exceptions\StatementNotSelectedException;
use ReflectionException;

/**
 * Trait ManipulationTrait
 *
 * @package Codememory\Components\Database\Orm\Repository\BasicQueries
 *
 * @author  Codememory
 */
trait ManipulationTrait
{

    /**
     * @param array $data
     *
     * @throws StatementNotSelectedException
     * @throws ReflectionException
     */
    public function insert(array $data): void
    {

        $qb = $this->createQueryBuilder();
        $record = array_map(function (mixed $columnName) {
            return sprintf(':%s', $columnName);
        }, array_keys($data));

        $qb
            ->setParameters($data)
            ->insert($this->getEntityData()->getTableName())
            ->setRecords(array_keys($data), ...$record)
            ->execute();

    }

    /**
     * @param array  $data
     * @param array  $by
     * @param string $expr
     *
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function update(array $data, array $by, string $expr = 'and'): void
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));
        $values = array_map(function (mixed $column) {
            return sprintf(':%s', $column);
        }, array_keys($data));

        $qb
            ->setParameters(array_merge($data, $by))
            ->update($this->getEntityData()->getTableName())
            ->updateData(array_keys($data), $values)
            ->where($qb->expression()->$expr(
                ...$this->constructionAssistant->getCollectedConditions($by, $qb)
            ))
            ->execute();

    }

    /**
     * @param array  $by
     * @param string $expr
     *
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function delete(array $by, string $expr = 'and'): void
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));

        $qb
            ->setParameters($by)
            ->delete()
            ->from($this->getEntityData()->getTableName())
            ->where($qb->expression()->$expr(
                ...$this->constructionAssistant->getCollectedConditions($by, $qb)
            ))
            ->execute();

    }

}