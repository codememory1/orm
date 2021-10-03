<?php

namespace Codememory\Components\Database\Orm\Repository;

use Codememory\Components\Database\Orm\QueryBuilder\ExtendedQueryBuilder;
use Codememory\Components\Database\QueryBuilder\Exceptions\NotSelectedStatementException;
use Codememory\Components\Database\QueryBuilder\Exceptions\QueryNotGeneratedException;
use Codememory\Components\Database\QueryBuilder\Interfaces\QueryBuilderInterface;
use ReflectionException;

/**
 * Trait BasicBuildersTrait
 *
 * @package Codememory\Components\Database\Orm\Repository
 *
 * @author  Codememory
 */
trait BasicBuildersTrait
{

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Insert Record
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array $data
     *
     * @return ExtendedQueryBuilder
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function insert(array $data): ExtendedQueryBuilder
    {

        $qb = $this->createQueryBuilder();
        $record = array_map(function (mixed $columnName) {
            return sprintf(':%s', $columnName);
        }, array_keys($data));

        $qb
            ->setParameters($data)
            ->insert($this->getEntityData()->getTableName())
            ->columns(...array_keys($data))
            ->records(...$record);

        $qb->generateQuery()->execute();

        return $qb;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Update entry conditionally
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array  $data
     * @param array  $by
     * @param string $expr
     *
     * @return ExtendedQueryBuilder
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function update(array $data, array $by, string $expr = 'and'): ExtendedQueryBuilder
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));
        $values = array_map(function (mixed $column) {
            return sprintf(':%s', $column);
        }, array_keys($data));

        $qb
            ->setParameters(array_merge($data, $by))
            ->update($this->getEntityData()->getTableName())
            ->setData(array_keys($data), $values)
            ->where($qb->expression()->$expr(
                ...$this->getCollectedConditions($by, $qb)
            ));

        $qb->generateQuery()->execute();

        return $qb;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Delete entry conditionally
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array  $by
     * @param string $expr
     *
     * @return ExtendedQueryBuilder
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function delete(array $by, string $expr = 'and'): ExtendedQueryBuilder
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));

        $qb
            ->setParameters($by)
            ->delete($this->getEntityData()->getTableName())
            ->where($qb->expression()->$expr(
                ...$this->getCollectedConditions($by, $qb)
            ));

        $qb->generateQuery()->execute();

        return $qb;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Count the number of records in a table
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array  $by
     * @param string $expr
     *
     * @return int
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     */
    public function getCount(array $by = [], string $expr = 'and'): int
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));
        $statement = $qb
            ->customSelect()
            ->columns(['count' => 'COUNT(*)'])->from('users');

        if ([] !== $by) {
            $statement->where($qb->expression()->$expr(
                ...$this->getCollectedConditions($by, $qb)
            ));
        }

        return $qb->generateQuery()->getResult()->toArray()[0]['count'];

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Basic conditional search
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array  $by
     * @param string $expr
     *
     * @return ExtendedQueryBuilder
     * @throws NotSelectedStatementException
     * @throws ReflectionException
     */
    protected function findBy(array $by, string $expr = 'and'): ExtendedQueryBuilder
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));

        $qb
            ->setParameters($by)
            ->select()
            ->from($this->getEntityData()->getTableName())
            ->where($qb->expression()->$expr(...$this->getCollectedConditions($by, $qb)));

        return $qb->generateQuery();

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns an array of collected conditions
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array                 $by
     * @param QueryBuilderInterface $queryBuilder
     *
     * @return array
     */
    private function getCollectedConditions(array $by, QueryBuilderInterface $queryBuilder): array
    {

        $conditions = [];

        foreach ($by as $columnName => $value) {
            $conditions[] = $queryBuilder->expression()->condition($columnName, '=', sprintf(':%s', $columnName));
        }

        return $conditions;

    }

}