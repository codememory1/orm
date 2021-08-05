<?php

namespace Codememory\Components\Database\Orm\QueryBuilder;

use Codememory\Components\Database\Orm\Constructions\Column;
use Codememory\Components\Database\Orm\Constructions\Join;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class ResultAsEntity
 *
 * @package Codememory\Components\Database\Orm\QueryBuilder
 *
 * @author  Codememory
 */
final class ResultAsEntity
{

    /**
     * @var object
     */
    private object $entity;

    /**
     * @var EntityDataInterface
     */
    private EntityDataInterface $entityData;

    /**
     * @var array
     */
    private array $records;

    /**
     * ResultAsEntity constructor.
     *
     * @param object              $entity
     * @param EntityDataInterface $entityData
     * @param array               $records
     */
    public function __construct(object $entity, EntityDataInterface $entityData, array $records)
    {

        $this->entity = $entity;
        $this->entityData = $entityData;
        $this->records = $records;

    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getResult(): array
    {

        foreach ($this->iterationOfRecords($this->records) as &$recordData) {
            $entityClone = $this->getEntityClone();
            $entityCloneReflector = $this->getEntityReflector($entityClone);

            $this->iterationOfRecord($recordData, $entityClone, $entityCloneReflector);

            $recordData = $entityClone;
        }

        return $this->records;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Iterate over records and call join handler if the array of properties by column names is empty
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array           $recordData
     * @param object          $entityClone
     * @param ReflectionClass $entityCloneReflector
     *
     * @throws ReflectionException
     */
    private function iterationOfRecord(array $recordData, object $entityClone, ReflectionClass $entityCloneReflector): void
    {

        foreach ($recordData as $columnName => $value) {
            $propertiesByColumnName = $this->getPropertiesByColumnName($columnName);

            if ([] !== $propertiesByColumnName) {
                $this->setValueForProperty($entityCloneReflector, $columnName, $entityClone, $value);
            } else {
                $propertyWithJoinByColumnName = $this->entityData->getJoinPropertyByColumns([$columnName]);

                if (false !== $propertyWithJoinByColumnName) {
                    $this->joinHandler($propertyWithJoinByColumnName, $entityClone, $columnName, $value);
                }
            }
        }

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Property handler that has the join attribute
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param ReflectionProperty $reflectionProperty
     * @param object             $entityClone
     * @param string             $columnName
     * @param mixed              $columnValue
     *
     * @throws ReflectionException
     */
    private function joinHandler(ReflectionProperty $reflectionProperty, object $entityClone, string $columnName, mixed $columnValue): void
    {

        $reflectionProperty->setAccessible(true);

        $joinAttribute = $this->entityData->getPropertyTarget()->getAttributeIfExist(Join::class, $reflectionProperty);
        $joinAttributeArguments = $this->entityData->getPropertyTarget()->getAttributeArguments($joinAttribute);
        $entityFromJoinArguments = $joinAttributeArguments['entity'];
        $columnsAsFromJoinArguments = array_combine($joinAttributeArguments['columns'], $joinAttributeArguments['as']);
        $propertyValueWithJoin = $reflectionProperty->getValue($entityClone) ?: new $entityFromJoinArguments();
        $propertyName = $columnsAsFromJoinArguments[$columnName] ?? $columnName;
        $propertyValueReflectorWithJoin = new ReflectionClass($propertyValueWithJoin);

        $this->setValueForProperty($propertyValueReflectorWithJoin, $propertyName, $propertyValueWithJoin, $columnValue);
        $this->setValueForProperty($reflectionProperty, $propertyName, $entityClone, $propertyValueWithJoin);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Set a value for a property
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param ReflectionClass|ReflectionProperty $reflector
     * @param string                             $propertyName
     * @param object                             $entity
     * @param mixed                              $value
     *
     * @throws ReflectionException
     */
    private function setValueForProperty(ReflectionClass|ReflectionProperty $reflector, string $propertyName, object $entity, mixed $value): void
    {

        if ($reflector instanceof ReflectionClass) {
            $property = $reflector->getProperty($propertyName);
        } else {
            $property = $reflector;
        }

        $property->setAccessible(true);

        $property->setValue($entity, $value);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns properties by column names
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $columnName
     *
     * @return array
     */
    private function getPropertiesByColumnName(string $columnName): array
    {

        return $this->entityData->getPropertyTarget()
            ->getPropertiesByAttributeWithArguments(Column::class, ['name' => $columnName]);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns reflector entity
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param object $entityClone
     *
     * @return ReflectionClass
     */
    private function getEntityReflector(object $entityClone): ReflectionClass
    {

        return new ReflectionClass($entityClone);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns a clone of an entity object
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return object
     */
    private function getEntityClone(): object
    {

        return clone $this->entity;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Iterating over records and returning a generator by reference
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array $records
     *
     * @return Generator
     */
    private function &iterationOfRecords(array &$records): Generator
    {

        foreach ($records as &$record) {
            yield $record;
        }

    }

}