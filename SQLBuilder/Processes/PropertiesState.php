<?php

namespace Codememory\Components\Database\Orm\SQLBuilder\Processes;

use Codememory\Components\ObjectComparison\Interfaces\PropertyComponentInterface;
use LogicException;

/**
 * Class PropertiesState
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder\Processes
 *
 * @author  Codememory
 */
class PropertiesState extends AbstractProcess
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
    public function setNameState(string $name): PropertiesState
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

        /** @var PropertyComponentInterface $propertyComponent */
        foreach ($this->entityComparison[$this->nameState]['properties'] as $propertyComponent) {
            $reflectionProperty = $propertyComponent->getReflector();

            if ($this->alterHelper->isColumn($reflectionProperty)) {
                call_user_func($handler, $reflectionProperty);
            }
        }

    }

}