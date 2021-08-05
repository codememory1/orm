<?php

namespace Codememory\Components\Database\Orm\SQLBuilder;

use Codememory\Components\Database\Schema\Statements\Definition\DropTable;
use ReflectionException;

/**
 * Class DropTableOfEntity
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder
 *
 * @author  Codememory
 */
class DropTableOfEntity extends AbstractBuilder
{

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function buildToArray(): array
    {

        $dropTable = new DropTable();

        return [$dropTable->table($this->entityData->getTableName())->getQuery()];

    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function buildToString(): string
    {

        return implode(';', $this->buildToArray());

    }

}