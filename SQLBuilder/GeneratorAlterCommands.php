<?php

namespace Codememory\Components\Database\Orm\SQLBuilder;

use Codememory\Components\Database\Orm\Constructions\Column;
use Codememory\Components\Database\Orm\Constructions\Entity;
use Codememory\Components\Database\Orm\Interfaces\ColumnComponentInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Orm\Interfaces\RelationshipInterface;
use Codememory\Components\Database\Schema\StatementComponents\Column as ColumnSchema;
use Codememory\Components\Database\Schema\Statements\Definition\AddToTable;
use Codememory\Components\Database\Schema\Statements\Definition\ChangeToTable;
use Codememory\Components\Database\Schema\Statements\Definition\DropToTable;
use Codememory\Components\Database\Schema\Statements\Definition\Rename as RenameSchema;
use Codememory\Components\ObjectComparison\Interfaces\ObjectInformationInterface;
use Codememory\Components\ObjectComparison\Interfaces\PropertyComponentInterface;
use JetBrains\PhpStorm\Pure;
use ReflectionAttribute;
use ReflectionException;
use ReflectionProperty;

/**
 * Class GeneratorAlterCommands
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder
 *
 * @author  Codememory
 */
class GeneratorAlterCommands
{

    /**
     * @var EntityDataInterface
     */
    private EntityDataInterface $entityData;

    /**
     * @var AlterHelper
     */
    private AlterHelper $alterHelper;

    /**
     * @param EntityDataInterface $entityData
     */
    #[Pure]
    public function __construct(EntityDataInterface $entityData)
    {

        $this->entityData = $entityData;
        $this->alterHelper = new AlterHelper($entityData);

    }

    /**
     * @var array
     */
    private array $queries = [];

    /**
     * @return array
     */
    public function getQueries(): array
    {

        return $this->queries;

    }

    /**
     * @return void
     */
    public function clearQueries(): void
    {

        $this->queries = [];

    }

    /**
     * @param object              $component
     * @param ReflectionAttribute $reflectionAttribute
     * @param array               $oldArguments
     * @param array               $currentArguments
     *
     * @return void
     */
    public function renameTable(object $component, ReflectionAttribute $reflectionAttribute, array $oldArguments, array $currentArguments): void
    {

        if ($component instanceof ObjectInformationInterface && $reflectionAttribute->getName() === Entity::class) {
            $renameSchema = new RenameSchema();

            $this->queries[] = $renameSchema
                ->table($oldArguments['tableName'])
                ->renameTable($currentArguments['tableName'])
                ->getQuery();
        }

    }

    /**
     * @param object              $component
     * @param ReflectionAttribute $reflectionAttribute
     * @param array               $oldArguments
     * @param string|null         $columnName
     *
     * @return void
     * @throws ReflectionException
     */
    public function changeColumn(object $component, ReflectionAttribute $reflectionAttribute, array $oldArguments, ?string $columnName): void
    {

        if ($component instanceof PropertyComponentInterface
            && $this->alterHelper->isPropertyAndColumn('property', $component->getReflector())
            && $this->alterHelper->isTypeAttribute($reflectionAttribute, ColumnComponentInterface::class)
            || $reflectionAttribute->getName() === Column::class) {
            $changeToTable = new ChangeToTable();
            $oldColumnName = $reflectionAttribute->getName() === Column::class ? $oldArguments['name'] : $columnName;

            $columnSchema = new ColumnSchema();
            $this->alterHelper->columnBuilder($component->getReflector(), $columnSchema);

            $changeToTable->table($this->entityData->getTableName())->changeColumn($oldColumnName, $columnSchema);

            $this->queries[] = $changeToTable->getQuery();
        }

    }

    /**
     * @param ReflectionAttribute $reflectionAttribute
     * @param object              $component
     * @param string|null         $columnName
     *
     * @return void
     * @throws ReflectionException
     */
    public function changeReference(ReflectionAttribute $reflectionAttribute, object $component, ?string $columnName): void
    {

        if ($this->alterHelper->isTypeAttribute($reflectionAttribute, RelationshipInterface::class)) {
            $this->dropReference($reflectionAttribute, $columnName);
            $this->addReference($reflectionAttribute, $component->getReflector());
        }

    }

    /**
     * @param ReflectionAttribute $attributeReflector
     * @param ReflectionProperty  $reflectionProperty
     *
     * @return void
     * @throws ReflectionException
     */
    public function addReference(ReflectionAttribute $attributeReflector, ReflectionProperty $reflectionProperty): void
    {

        if ($this->alterHelper->isTypeAttribute($attributeReflector, RelationshipInterface::class)) {
            $this->queries[] = $this->alterHelper->createReference($reflectionProperty);
        }

    }

    /**
     * @param ReflectionAttribute $attributeReflector
     * @param string              $constraintName
     *
     * @return void
     */
    public function dropReference(ReflectionAttribute $attributeReflector, string $constraintName): void
    {

        if ($this->alterHelper->isTypeAttribute($attributeReflector, RelationshipInterface::class)) {
            $this->queries[] = $this->alterHelper->removeReference($constraintName);
        }

    }

    /**
     * @param ReflectionProperty $reflectionProperty
     *
     * @return void
     * @throws ReflectionException
     */
    public function addColumn(ReflectionProperty $reflectionProperty): void
    {

        $columnSchema = new ColumnSchema();
        $addToTable = new AddToTable();

        $this->alterHelper->columnBuilder($reflectionProperty, $columnSchema);

        $this->queries[] = $addToTable
            ->table($this->entityData->getTableName())
            ->addColumn($columnSchema)
            ->getQuery();

    }

    /**
     * @param string $columnName
     *
     * @return void
     */
    public function dropColumn(string $columnName): void
    {

        $dropToTable = new DropToTable();

        $this->queries[] = $dropToTable
            ->table($this->entityData->getTableName())
            ->dropColumn($columnName)
            ->getQuery();

    }

    /**
     * @param ReflectionAttribute $reflectionAttribute
     * @param ReflectionProperty  $reflectionProperty
     *
     * @return void
     * @throws ReflectionException
     */
    public function modifyColumn(ReflectionAttribute $reflectionAttribute, ReflectionProperty $reflectionProperty): void
    {

        if ($this->alterHelper->isTypeAttribute($reflectionAttribute, ColumnComponentInterface::class)) {
            $columnSchema = new ColumnSchema();
            $changeToTable = new ChangeToTable();

            $this->alterHelper->columnBuilder($reflectionProperty, $columnSchema);

            $this->queries[] = $changeToTable
                ->table($this->entityData->getTableName())
                ->modifyColumn($columnSchema)
                ->getQuery();
        }

    }

}