<?php

namespace Codememory\Components\Database\Orm\SQLBuilder\Processes;

use Codememory\Components\ObjectComparison\Interfaces\AttributeInterface;

/**
 * Class ModifyAttributeArguments
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder\Processes
 *
 * @author  Codememory
 */
class ModifyAttributeArguments extends AbstractProcess
{

    /**
     * @inheritDoc
     */
    public function process(callable $handler): void
    {

        foreach ($this->entityComparison['changes']['attributeArguments'] as $attributeArgumentData) {
            $component = $attributeArgumentData['component'];
            $oldArguments = $attributeArgumentData['oldArguments'];
            $arguments = $attributeArgumentData['arguments'];
            /** @var AttributeInterface $to */
            $to = $attributeArgumentData['to'];

            call_user_func($handler, $to->getReflectionAttribute(), $arguments, $oldArguments, $component);
        }

    }

}