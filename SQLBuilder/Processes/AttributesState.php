<?php

namespace Codememory\Components\Database\Orm\SQLBuilder\Processes;

use Codememory\Components\ObjectComparison\Interfaces\AttributeInterface;
use LogicException;

/**
 * Class AttributesState
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder\Processes
 *
 * @author  Codememory
 */
class AttributesState extends AbstractProcess
{

    /**
     * @var string|null
     */
    private ?string $nameState = null;

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setNameState(string $name): AttributesState
    {

        $this->nameState = $name;

        return $this;

    }

    /**
     * @inheritDoc
     */
    public function process(callable $handler): void
    {

        if (null === $this->nameState) {
            throw new LogicException('State name not specified for process');
        }

        foreach ($this->entityComparison[$this->nameState]['attributes'] as $attributeData) {
            /** @var AttributeInterface $attribute */
            $attribute = $attributeData['attribute'];
            $attributeReflection = $attribute->getReflectionAttribute();

            if ($this->alterHelper->isPropertyAndColumn($attributeData['toComponentName'], $attributeData['to']->getReflector())) {
                $reflectionProperty = $attributeData['to']->getReflector();

                call_user_func($handler, $attributeReflection, $reflectionProperty);
            }
        }

    }

}