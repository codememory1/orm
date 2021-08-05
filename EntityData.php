<?php

namespace Codememory\Components\Database\Orm;

use Codememory\Components\Attributes\AttributeAssistant;
use Codememory\Components\Attributes\Interfaces\AssistantInterface;
use Codememory\Components\Attributes\Targets\ClassTarget;
use Codememory\Components\Attributes\Targets\PropertyTarget;
use Codememory\Components\Database\Orm\Constructions\Entity;
use Codememory\Components\Database\Orm\Constructions\Join;
use Codememory\Components\Database\Orm\Constructions\Repository;
use Codememory\Components\Database\Orm\Exceptions\ObjectIsNotEntityException;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use ReflectionException;
use ReflectionProperty;

/**
 * Class EntityData
 *
 * @package Codememory\Components\Database\Orm
 *
 * @author  Codememory
 */
class EntityData implements EntityDataInterface
{

    /**
     * @var string|object
     */
    private string|object $entity;

    /**
     * @var AssistantInterface
     */
    private AssistantInterface $attributeAssistant;

    /**
     * @var ClassTarget
     */
    private ClassTarget $classTarget;

    /**
     * @var PropertyTarget
     */
    private PropertyTarget $propertyTarget;

    /**
     * EntityData constructor.
     *
     * @param string|object $entity
     *
     * @throws ReflectionException
     */
    public function __construct(string|object $entity)
    {

        $this->entity = $entity;
        $this->attributeAssistant = new AttributeAssistant();
        $this->attributeAssistant->setPursued($entity);

        $this->classTarget = new ClassTarget($this->attributeAssistant);
        $this->propertyTarget = new PropertyTarget($this->attributeAssistant);

    }

    /**
     * @inheritDoc
     */
    public function getEntity(): object
    {

        $entity = $this->entity;

        return is_string($this->entity) ? new $entity() : $entity;

    }

    /**
     * @inheritDoc
     * @throws ObjectIsNotEntityException
     */
    public function getEntityName(): string
    {

        if(!$this->isEntity()) {
            throw new ObjectIsNotEntityException($this->getEntity());
        }

        return $this->attributeAssistant->getReflector()->getShortName();

    }

    /**
     * @inheritDoc
     */
    public function getAttributeAssistant(): AssistantInterface
    {

        return $this->attributeAssistant;

    }

    /**
     * @inheritDoc
     */
    public function getClassTarget(): ClassTarget
    {

        return $this->classTarget;

    }

    /**
     * @inheritDoc
     */
    public function getPropertyTarget(): PropertyTarget
    {

        return $this->propertyTarget;

    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function getTableName(): ?string
    {

        $entityAttribute = $this->classTarget->getAttributeIfExist(Entity::class);

        if ($entityAttribute) {
            return $this->classTarget->getAttributeArguments($entityAttribute)['tableName'];
        }

        return null;

    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function getNamespaceRepository(): ?string
    {

        $repositoryAttribute = $this->classTarget->getAttributeIfExist(Repository::class);

        if ($repositoryAttribute) {
            return $this->classTarget->getAttributeArguments($repositoryAttribute)['repository'];
        }

        return null;

    }

    /**
     * @inheritDoc
     */
    public function isEntity(): bool
    {

        $attributesToEntity = $this->classTarget->getAttributes();

        return $this->classTarget->existAttribute(Entity::class, $attributesToEntity);

    }

    /**
     * @inheritDoc
     */
    public function existRepository(): bool
    {

        $attributesToEntity = $this->classTarget->getAttributes();

        return $this->classTarget->existAttribute(Repository::class, $attributesToEntity);

    }

    /**
     * @inheritDoc
     */
    public function getJoinProperties(): array
    {

        return $this->propertyTarget->getPropertiesIfAttributesExist(Join::class);

    }

    /**
     * @inheritDoc
     */
    public function getJoinPropertyByColumns(array $columns): bool|ReflectionProperty
    {

        $joinProperties = $this->getJoinProperties();

        /** @var ReflectionProperty $joinProperty */
        foreach ($joinProperties as $joinProperty) {
            $joinAttribute = $this->propertyTarget->getAttributeIfExist(Join::class, $joinProperty);
            $joinArguments = $this->propertyTarget->getAttributeArguments($joinAttribute);

            foreach ($columns as $column) {
                if (in_array($column, $joinArguments['columns'])) {
                    return $joinProperty;
                }
            }
        }

        return false;

    }

}