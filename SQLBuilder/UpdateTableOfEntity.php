<?php

namespace Codememory\Components\Database\Orm\SQLBuilder;

use Codememory\Components\Caching\Exceptions\ConfigPathNotExistException;
use Codememory\Components\Database\Orm\EntityCache;
use Codememory\Components\Database\Orm\SQLBuilder\Processes\AttributesState;
use Codememory\Components\Database\Orm\SQLBuilder\Processes\ModifyAttributeArguments;
use Codememory\Components\Database\Orm\SQLBuilder\Processes\PropertiesState;
use Codememory\Components\ObjectComparison\ObjectComparison;
use ReflectionAttribute;
use ReflectionProperty;

/**
 * Class UpdateTableOfEntity
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder
 *
 * @author  Codememory
 */
class UpdateTableOfEntity extends AbstractBuilder
{

    /**
     * @inheritDoc
     * @throws ConfigPathNotExistException
     */
    public function buildToArray(): array
    {

        $entityComparison = $this->getEntityAndCacheComparison()->compare()->getComparisonResult();

        return $this->comparisonDataProcessing($entityComparison);

    }

    /**
     * @inheritDoc
     * @throws ConfigPathNotExistException
     */
    public function buildToString(): string
    {

        return implode(';', $this->buildToArray());

    }

    /**
     * @return array|object
     * @throws ConfigPathNotExistException
     */
    private function getEntityCache(): array|object
    {

        $entityCache = new EntityCache($this->entityData);

        return $entityCache->getEntityCache();

    }

    /**
     * @return ObjectComparison
     * @throws ConfigPathNotExistException
     */
    private function getEntityAndCacheComparison(): ObjectComparison
    {

        $entityCache = $this->getEntityCache();

        if ([] !== $entityCache) {
            return new ObjectComparison($entityCache, $this->entity);
        }

        return new ObjectComparison($this->entity, $this->entity);

    }

    /**
     * @param array $entityComparison
     *
     * @return array
     */
    private function comparisonDataProcessing(array $entityComparison): array
    {

        $generatorAlterCommands = new GeneratorAlterCommands($this->entityData);

        // Calling processes
        $this->callProcesses(
            new ModifyAttributeArguments($entityComparison, $this->entityData),
            new PropertiesState($entityComparison, $this->entityData),
            new AttributesState($entityComparison, $this->entityData),
            $generatorAlterCommands
        );

        return $generatorAlterCommands->getQueries();

    }

    /**
     * @param ModifyAttributeArguments $modifyAttributeArguments
     * @param PropertiesState          $propertiesState
     * @param AttributesState          $attributesState
     * @param GeneratorAlterCommands   $generatorAlterCommands
     *
     * @return void
     */
    private function callProcesses(ModifyAttributeArguments $modifyAttributeArguments, PropertiesState $propertiesState, AttributesState $attributesState, GeneratorAlterCommands $generatorAlterCommands): void
    {

        $modifyAttributeArguments->process(function (ReflectionAttribute $reflectionAttribute,
                                                     array               $currentArguments,
                                                     array               $oldArguments,
                                                     object              $component) use ($generatorAlterCommands) {
            $columnName = $this->alterHelper->getColumnNameIfProperty($component);

            $generatorAlterCommands->renameTable($component, $reflectionAttribute, $oldArguments, $currentArguments);
            $generatorAlterCommands->changeColumn($component, $reflectionAttribute, $oldArguments, $columnName);
            $generatorAlterCommands->changeReference($reflectionAttribute, $component, $columnName);
        });

        $propertiesState->setNameState('remotely')->process(function (ReflectionProperty $reflectionProperty) use ($generatorAlterCommands) {
            $generatorAlterCommands->dropColumn($this->alterHelper->getColumnName($reflectionProperty));
        });

        $propertiesState->setNameState('added')->process(function (ReflectionProperty $reflectionProperty) use ($generatorAlterCommands) {
            $generatorAlterCommands->addColumn($reflectionProperty);
        });

        $attributesState->setNameState('remotely')->process(function (ReflectionAttribute $reflectionAttribute,
                                                                      ReflectionProperty  $reflectionProperty) use ($generatorAlterCommands) {
            $generatorAlterCommands->modifyColumn($reflectionAttribute, $reflectionProperty);
            $generatorAlterCommands->dropReference($reflectionAttribute, $this->alterHelper->getColumnName($reflectionProperty));
        });

        $attributesState->setNameState('added')->process(function (ReflectionAttribute $reflectionAttribute,
                                                                   ReflectionProperty  $reflectionProperty) use ($generatorAlterCommands) {
            $generatorAlterCommands->modifyColumn($reflectionAttribute, $reflectionProperty);
            $generatorAlterCommands->addReference($reflectionAttribute, $reflectionProperty);
        });

    }

}