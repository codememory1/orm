<?php

namespace Codememory\Components\Database\Orm\Repository;

use Codememory\Components\Database\QueryBuilder\QueryBuilder;

/**
 * Class ConstructionAssistant
 *
 * @package Codememory\Components\Database\Orm\Repository
 *
 * @author  Codememory
 */
class ConstructionAssistant
{

    /**
     * @param array        $by
     * @param QueryBuilder $queryBuilder
     *
     * @return array
     */
    final public function getCollectedConditions(array $by, QueryBuilder $queryBuilder): array
    {

        $conditions = [];

        foreach ($by as $columnName => $value) {
            $conditions[] = $queryBuilder->expression()->condition($columnName, '=', sprintf(':%s', $columnName));
        }

        return $conditions;

    }

}