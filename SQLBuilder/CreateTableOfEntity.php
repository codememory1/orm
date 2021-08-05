<?php

namespace Codememory\Components\Database\Orm\SQLBuilder;

use Codememory\Components\Database\Orm\Constructions\Collate;
use Codememory\Components\Database\Schema\StatementComponents\Column as ColumnSchema;
use Codememory\Components\Database\Schema\Statements\Definition\CreateTable;
use ReflectionException;

/**
 * Class CreateTableOfEntity
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder
 *
 * @author  Codememory
 */
class CreateTableOfEntity extends AbstractBuilder
{

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function buildToArray(): array
    {

        $queries = [];
        $properties = $this->entityData->getAttributeAssistant()->getProperties();
        $tableCreator = new CreateTable();
        $columnSchema = new ColumnSchema();

        foreach ($properties as $property) {
            if ($this->alterHelper->isColumn($property)) {
                $this->alterHelper->columnBuilder($property, $columnSchema);
            }
        }

        $this->tableBuilder($tableCreator, $columnSchema);

        $queries[] = $tableCreator->getQuery();

        foreach ($properties as $property) {
            if ($this->alterHelper->isColumn($property)) {
                $reference = $this->alterHelper->createReference($property);

                if (false !== $reference) {
                    $queries[] = $reference;
                }
            }
        }

        return $queries;

    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function buildToString(): string
    {

        return implode(';', $this->buildToArray());

    }

    /**
     * @param CreateTable  $tableCreator
     * @param ColumnSchema $columnSchema
     *
     * @throws ReflectionException
     */
    private function tableBuilder(CreateTable $tableCreator, ColumnSchema $columnSchema): void
    {

        $tableCollateArguments = $this->alterHelper->getAttributeArgumentsFromClass(Collate::class);
        $tableCreator
            ->table($this->entityData->getTableName())
            ->columns($columnSchema);

        if (false !== $tableCollateArguments) {
            $tableCreator->collate($tableCollateArguments['collate']);
        }

    }

}