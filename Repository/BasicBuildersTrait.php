<?php

namespace Codememory\Components\Database\Orm\Repository;

use Codememory\Components\Database\Orm\QueryBuilder\ExtendedQueryBuilder;
use Codememory\Components\Database\QueryBuilder\Exceptions\NotSelectedStatementException;
use Codememory\Components\Database\QueryBuilder\Exceptions\QueryNotGeneratedException;
use Codememory\Components\Database\QueryBuilder\Interfaces\QueryBuilderInterface;
use Codememory\Components\Database\Schema\Interfaces\SelectInterface;
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
     * @throws ReflectionException
     */
    public function getCount(array $by = [], string $expr = 'and'): int
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));
        $statement = $qb
            ->customSelect()
            ->columns(['count' => 'COUNT(*)'])->from($this->getEntityData()->getTableName());

        if ([] !== $by) {
            $statement->where($qb->expression()->$expr(
                ...$this->getCollectedConditions($by, $qb)
            ));
        }

        return $qb->generateQuery()->getResult()->toArray()[0]['count'];

    }

    /**
     * @param string $column
     *
     * @return int
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function getMin(string $column): int
    {

        return $this->getExtremum('max', $column);

    }

    /**
     * @param string $column
     *
     * @return int
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function getMax(string $column): int
    {

        return $this->getExtremum('max', $column);

    }

    /**
     * @return int
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function getMinId(): int
    {

        return $this->getMin('id');

    }

    /**
     * @return int
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function getMaxId(): int
    {

        return $this->getMax('id');

    }

    /**
     * @param string $func
     * @param string $column
     *
     * @return int
     * @throws NotSelectedStatementException
     * @throws QueryNotGeneratedException
     * @throws ReflectionException
     */
    public function getExtremum(string $func, string $column): int
    {

        $fullFunction = sprintf('%s(%s)', ucfirst($func), $column);

        $qb = $this->createQueryBuilder();
        $qb
            ->customSelect()
            ->columns(['num' => $fullFunction])->from($this->getEntityData()->getTableName());

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

        return $this->findByWithoutGenerate($by, $expr)->generateQuery();

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Searches on a QueryBuilder condition without generating a query
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array  $by
     * @param string $expr
     *
     * @return ExtendedQueryBuilder
     * @throws ReflectionException
     */
    protected function findByWithoutGenerate(array $by, string $expr = 'and'): ExtendedQueryBuilder
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));

        $this->findByWithStatement($qb, $by, $expr);

        return $qb;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Searches for a condition and returns a statement
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param QueryBuilderInterface $queryBuilder
     * @param array                 $by
     * @param string                $expr
     *
     * @return SelectInterface
     * @throws ReflectionException
     */
    protected function findByWithStatement(QueryBuilderInterface $queryBuilder, array $by, string $expr = 'and'): SelectInterface
    {

        $expr = sprintf('expr%s', ucfirst($expr));

        return $queryBuilder
            ->setParameters($by)
            ->select()
            ->from($this->getEntityData()->getTableName())
            ->where($queryBuilder->expression()->$expr(...$this->getCollectedConditions($by, $queryBuilder)));

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