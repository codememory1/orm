<?php

namespace Codememory\Components\Database\Orm;

use Codememory\Components\Attributes\AttributeAssistant;
use Codememory\Components\Attributes\Targets\ClassTarget;
use Codememory\Components\Attributes\Targets\PropertyTarget;
use Codememory\Components\Database\Connection\Interfaces\ConnectorInterface;
use Codememory\Components\Database\Orm\Constructions\Column;
use Codememory\Components\Database\Orm\Constructions\Entity;
use Codememory\Components\Database\Schema\Schema;
use Codememory\Support\Str;
use Exception;
use ReflectionException;

/**
 * Class Flush
 *
 * @package Codememory\Components\Database\Orm
 *
 * @author  Codememory
 */
final class Flush
{

    /**
     * @var ConnectorInterface
     */
    private ConnectorInterface $connector;

    /**
     * @var array
     */
    private array $commits;

    /**
     * Flush constructor.
     *
     * @param ConnectorInterface $connector
     * @param array              $commits
     */
    public function __construct(ConnectorInterface $connector, array $commits)
    {

        $this->connector = $connector;
        $this->commits = $commits;

    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function flush(): void
    {

        $schema = new Schema();

        foreach ($this->getTablesWithRecords() as $tableName => $tableWithRecords) {
            foreach ($tableWithRecords['records'] as $recordIndex => $recordValues) {
                foreach ($recordValues as $columnName => $recordData) {
                    if (null === $recordData['value']) {
                        unset($tableWithRecords['columns'][$columnName]);
                        unset($tableWithRecords['records'][$recordIndex][$columnName]);
                    }
                }
            }

            $schema->insert()
                ->table($tableName)
                ->columns(...$tableWithRecords['columns'])
                ->records(...$this->getRecordValues($tableWithRecords));

            $this->connector->getConnection()
                ->prepare($schema->__toString())
                ->execute($this->getRecordParameters($tableWithRecords));
        }

    }

    /**
     * @return array
     * @throws ReflectionException
     * @throws Exception
     */
    private function getTablesWithRecords(): array
    {

        $attributeAssistant = new AttributeAssistant();
        $classTarget = new ClassTarget($attributeAssistant);
        $tablesWithRecords = [];

        foreach ($this->commits as $commit) {
            $attributeAssistant->setPursued($commit);

            $tableName = $classTarget->getAttributeArguments($classTarget->getAttributeIfExist(Entity::class))['tableName'];
            $records = $this->getRecordsOfEntity($attributeAssistant, $tablesWithRecords, $tableName, $commit);

            $tablesWithRecords[$tableName]['records'][] = $records;
        }

        return $tablesWithRecords;

    }

    /**
     * @param AttributeAssistant $attributeAssistant
     * @param array              $tablesWithRecords
     * @param string             $tableName
     * @param object             $commit
     *
     * @return array
     * @throws Exception
     */
    private function getRecordsOfEntity(AttributeAssistant $attributeAssistant, array &$tablesWithRecords, string $tableName, object $commit): array
    {

        $records = [];
        $propertyTarget = new PropertyTarget($attributeAssistant);

        $properties = $propertyTarget->getPropertiesIfAttributesExist(Column::class);

        foreach ($properties as $property) {
            $property->setAccessible(true);

            $columnName = $property->getName();

            $tablesWithRecords[$tableName]['columns'][$columnName] = $columnName;
            $records[$columnName] = [
                'hash'  => Str::random(10),
                'value' => $property->getValue($commit)
            ];
        }

        return $records;

    }

    /**
     * @param array $tableWithRecords
     *
     * @return array
     */
    private function getRecordValues(array $tableWithRecords): array
    {

        return array_map(function (array $record) {
            return array_map(function (string $key) use ($record) {
                return sprintf(':%s', $record[$key]['hash']);
            }, array_keys($record));
        }, $tableWithRecords['records']);

    }

    /**
     * @param array $tableWithRecords
     *
     * @return array
     */
    private function getRecordParameters(array $tableWithRecords): array
    {

        $parameters = [];

        foreach ($tableWithRecords['records'] as $record) {
            foreach ($record as $key => $value) {
                $parameters[$value['hash']] = $value['value'];
            }
        }

        return $parameters;

    }

}