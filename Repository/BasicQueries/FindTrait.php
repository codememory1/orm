<?php

namespace Codememory\Components\Database\Orm\Repository\BasicQueries;

use Codememory\Components\Database\Orm\QueryBuilder\Answer\ResultTo;
use Codememory\Components\Database\Orm\QueryBuilder\ExtendedQueryBuilder;
use Codememory\Components\Database\QueryBuilder\Exceptions\StatementNotSelectedException;
use ReflectionException;

/**
 * Trait FindTrait
 *
 * @package Codememory\Components\Database\Orm\Repository\BasicQueries
 *
 * @author  Codememory
 */
trait FindTrait
{

    /**
     * @return ResultTo
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function customFindAll(): ResultTo
    {

        return $this->find()->generateTo();

    }

    /**
     * @return array
     * @throws StatementNotSelectedException
     * @throws ReflectionException
     */
    public function findAll(): array
    {

        return $this->customFindAll()->entity()->all();

    }

    /**
     * @param int $id
     *
     * @return ResultTo
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function customFindById(int $id): ResultTo
    {

        $qb = $this->find()
            ->setParameter('id', $id)
            ->where(
                $this->queryBuilder->expression()->exprAnd(
                    $this->queryBuilder->expression()->condition('id', '=', ':id')
                )
            );

        return $qb->generateTo();

    }

    /**
     * @param int $id
     *
     * @return object|bool
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function findById(int $id): object|bool
    {

        return $this->customFindById($id)->entity()->first();

    }

    /**
     * @param array  $by
     * @param string $expr
     *
     * @return ResultTo
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function customFindBy(array $by, string $expr = 'and'): ResultTo
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));

        $qb
            ->setParameters($by)
            ->select()
            ->from($this->getEntityData()->getTableName())
            ->where(
                $qb->expression()->$expr(
                    ...$this->constructionAssistant->getCollectedConditions($by, $qb)
                )
            );

        return $qb->generateTo();

    }

    /**
     * @param array  $by
     * @param string $expr
     *
     * @return array
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    public function findBy(array $by, string $expr = 'and'): array
    {

        return $this->customFindBy($by, $expr)->entity()->all();

    }

    /**
     * @return ExtendedQueryBuilder
     * @throws ReflectionException
     * @throws StatementNotSelectedException
     */
    private function find(): ExtendedQueryBuilder
    {

        $qb = $this->createQueryBuilder();

        $qb->select()->from($this->getEntityData()->getTableName());

        return $qb;

    }

}