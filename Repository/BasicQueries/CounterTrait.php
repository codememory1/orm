<?php

namespace Codememory\Components\Database\Orm\Repository\BasicQueries;

use Codememory\Components\Database\QueryBuilder\Exceptions\StatementNotSelectedException;
use ReflectionException;

/**
 * Trait CounterTrait
 *
 * @package Codememory\Components\Database\Orm\Repository\BasicQueries
 *
 * @author  Codememory
 */
trait CounterTrait
{

    /**
     * @param array  $by
     * @param string $expr
     *
     * @return int
     * @throws StatementNotSelectedException
     * @throws ReflectionException
     */
    public function getTotalRecords(array $by = [], string $expr = 'and'): int
    {

        $qb = $this->createQueryBuilder();
        $expr = sprintf('expr%s', ucfirst($expr));

        $qb->select(['count' => 'COUNT(*)'])->from($this->getEntityData()->getTableName());

        if ([] !== $by) {
            $qb->where(
                $qb->expression()->$expr(
                    ...$this->constructionAssistant->getCollectedConditions($by, $qb)
                )
            );
        }

        return $qb->generateTo()->array()->first()['count'];

    }

}