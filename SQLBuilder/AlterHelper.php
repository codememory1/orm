<?php

namespace Codememory\Components\Database\Orm\SQLBuilder;

use Codememory\Components\Attributes\Targets\PropertyTarget;
use Codememory\Components\Database\Orm\Constructions\AI;
use Codememory\Components\Database\Orm\Constructions\Collate;
use Codememory\Components\Database\Orm\Constructions\Column;
use Codememory\Components\Database\Orm\Constructions\DefaultValue;
use Codememory\Components\Database\Orm\Constructions\Identifier;
use Codememory\Components\Database\Orm\Constructions\Primary;
use Codememory\Components\Database\Orm\Constructions\Reference;
use Codememory\Components\Database\Orm\Constructions\Unique;
use Codememory\Components\Database\Orm\Constructions\Visible;
use Codememory\Components\Database\Orm\EntityData;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Schema\Interfaces\ColumnDefinitionInterface;
use Codememory\Components\Database\Schema\Interfaces\ReferenceDefinitionInterface;
use Codememory\Components\Database\Schema\StatementComponents\Column as ColumnSchema;
use Codememory\Components\Database\Schema\StatementComponents\Reference as ReferenceSchema;
use Codememory\Components\Database\Schema\Statements\Definition\AddToTable;
use Codememory\Components\Database\Schema\Statements\Definition\DropToTable;
use Codememory\Components\ObjectComparison\Interfaces\PropertyComponentInterface;
use JetBrains\PhpStorm\Pure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class AlterHelper
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder
 *
 * @author  Codememory
 */
class AlterHelper
{

    /**
     * @var EntityDataInterface
     */
    private EntityDataInterface $entityData;

    /**
     * @param EntityDataInterface $entityData
     */
    public function __construct(EntityDataInterface $entityData)
    {

        $this->entityData = $entityData;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns the name of the column if the component is a property
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param object $component
     *
     * @return string|null
     * @throws ReflectionException
     */
    public function getColumnNameIfProperty(object $component): ?string
    {

        if ($component instanceof PropertyComponentInterface && $this->isColumn($component->getReflector())) {
            return $this->getColumnName($component->getReflector());
        }

        return null;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns the name of a column from a property
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param ReflectionProperty $propertyReflector
     *
     * @return string
     * @throws ReflectionException
     */
    public function getColumnName(ReflectionProperty $propertyReflector): string
    {

        return $this->getAttributeArgumentsFromProperty(Column::class, $propertyReflector)['name'];

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns target properties to work with property attributes
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return PropertyTarget
     */
    public function getPropertyTarget(): PropertyTarget
    {

        return $this->entityData->getPropertyTarget();

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns an array of attribute arguments from properties
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string             $attributeName
     * @param ReflectionProperty $reflectionProperty
     *
     * @return bool|array
     * @throws ReflectionException
     */
    public function getAttributeArgumentsFromProperty(string $attributeName, ReflectionProperty $reflectionProperty): bool|array
    {

        $defaultValueAttribute = $this->entityData->getPropertyTarget()->getAttributeIfExist($attributeName, $reflectionProperty);

        if (false === $defaultValueAttribute) {
            return false;
        }

        return $this->entityData->getPropertyTarget()->getAttributeArguments($defaultValueAttribute);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns an array of arguments from a class attribute
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $attributeName
     *
     * @return bool|array
     * @throws ReflectionException
     */
    public function getAttributeArgumentsFromClass(string $attributeName): bool|array
    {

        $defaultValueAttribute = $this->entityData->getClassTarget()->getAttributeIfExist($attributeName);

        if (false === $defaultValueAttribute) {
            return false;
        }

        return $this->entityData->getPropertyTarget()->getAttributeArguments($defaultValueAttribute);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns an array of names from an array of properties
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param ReflectionProperty[] $properties
     *
     * @return array
     */
    #[Pure]
    public function getPropertyNames(array $properties): array
    {

        $names = [];

        foreach ($properties as $property) {
            $names[] = $property->getName();
        }

        return $names;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Check the existence of at least one attribute in a property
     * from those specified in the array
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array              $attributes
     * @param ReflectionProperty $reflectionProperty
     *
     * @return bool
     */
    public function existAttributeOneOf(array $attributes, ReflectionProperty $reflectionProperty): bool
    {

        $propertyTarget = $this->getPropertyTarget();

        foreach ($attributes as $attribute) {
            if ($propertyTarget->existAttribute($attribute, $propertyTarget->getAttributes($reflectionProperty))) {
                return true;
            }
        }

        return false;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Assembling the query column, from the attributes of the property
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param ReflectionProperty $reflectionProperty
     * @param ColumnSchema       $columnSchema
     *
     * @return void
     * @throws ReflectionException
     */
    public function columnBuilder(ReflectionProperty $reflectionProperty, ColumnSchema $columnSchema): void
    {

        $propertyTarget = $this->getPropertyTarget();

        $columnArguments = $this->getAttributeArgumentsFromProperty(Column::class, $reflectionProperty);
        $defaultValueArguments = $this->getAttributeArgumentsFromProperty(DefaultValue::class, $reflectionProperty);
        $visibleInVisibleArguments = $this->getAttributeArgumentsFromProperty(Visible::class, $reflectionProperty);
        $columnCollateArguments = $this->getAttributeArgumentsFromProperty(Collate::class, $reflectionProperty);

        $columnType = $columnArguments['type'];

        /** @var ColumnDefinitionInterface $columnCreated */
        $columnCreated = $columnSchema
            ->setColumnName($columnArguments['name'])
            ->$columnType($columnArguments['length']);

        if ($columnArguments['nullable']) {
            $columnCreated->null();
        } else {
            $columnCreated->notNull();
        }

        if (false !== $defaultValueArguments) {
            $columnCreated->default($defaultValueArguments['value']);
        }

        if (false !== $visibleInVisibleArguments) {
            if ($visibleInVisibleArguments['visible']) {
                $columnCreated->visible();
            } else {
                $columnCreated->invisible();
            }
        }

        if ($propertyTarget->existAttribute(Identifier::class, $propertyTarget->getAttributes($reflectionProperty))) {
            $columnCreated->increment()->primary();
        }

        if ($propertyTarget->existAttribute(AI::class, $propertyTarget->getAttributes($reflectionProperty))) {
            $columnCreated->increment();
        }

        if ($propertyTarget->existAttribute(Primary::class, $propertyTarget->getAttributes($reflectionProperty))) {
            $columnCreated->primary();
        }

        if ($this->existAttributeOneOf([Unique::class], $reflectionProperty)) {
            $columnCreated->unique();
        }

        if (false !== $columnCollateArguments) {
            $columnCreated->collate($columnCollateArguments['collate']);
        }

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Check properties on columns, i.e., the existence of the Column attribute in the property
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param ReflectionProperty $property
     *
     * @return bool
     */
    public function isColumn(ReflectionProperty $property): bool
    {

        $propertyAttributes = $this->entityData->getPropertyTarget()->getAttributes($property);

        return $this->entityData->getPropertyTarget()->existAttribute(Column::class, $propertyAttributes);

    }

    /**
     * @param string             $componentName
     * @param ReflectionProperty $property
     *
     * @return bool
     */
    public function isPropertyAndColumn(string $componentName, ReflectionProperty $property): bool
    {

        return $componentName === 'property' && $this->isColumn($property);

    }

    /**
     * @param ReflectionAttribute $attribute
     * @param string              $interface
     *
     * @return bool
     */
    public function isTypeAttribute(ReflectionAttribute $attribute, string $interface): bool
    {

        $attributeClass = $attribute->newInstance();
        $reflector = new ReflectionClass($attributeClass);

        if (!interface_exists($interface)) {
            return false;
        }

        return $reflector->implementsInterface($interface);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns sql foreign key creation and reference
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param ReflectionProperty $property
     *
     * @return bool|string|null
     * @throws ReflectionException
     */
    public function createReference(ReflectionProperty $property): bool|string|null
    {

        $propertyTarget = $this->entityData->getPropertyTarget();
        $columnName = $this->getAttributeArgumentsFromProperty(Column::class, $property)['name'];

        if ($propertyTarget->existAttribute(Reference::class, $propertyTarget->getAttributes($property))) {
            $referenceSchema = new ReferenceSchema();
            $referenceArguments = $this->getAttributeArgumentsFromProperty(Reference::class, $property);
            $entityDataWithReference = new EntityData($referenceArguments['entity']);
            $referencedTableName = $entityDataWithReference->getTableName();

            $referenceSchema->add(function (ReferenceDefinitionInterface $definition) use ($columnName, $referencedTableName, $referenceArguments) {
                $definition
                    ->constraint(sprintf('%s_fk', $columnName))
                    ->foreignKeys($columnName)
                    ->table($referencedTableName)
                    ->internalKeys($referenceArguments['referencedColumnName']);

                if ([] !== $referenceArguments['on']) {
                    foreach ($referenceArguments['on'] as $index => $on) {
                        $definition->$on($referenceArguments['onOptions'][$index]);
                    }
                }
            });

            return (new AddToTable())->table($this->entityData->getTableName())->addReference($referenceSchema)->getQuery();
        }

        return false;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns sql delete foreign key
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $constraint
     *
     * @return string|null
     */
    public function removeReference(string $constraint): ?string
    {

        return (new DropToTable())
            ->table($this->entityData->getTableName())
            ->dropForeign(sprintf('%s_fk', $constraint))
            ->getQuery();

    }

}