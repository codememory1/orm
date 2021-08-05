<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;
use Codememory\Components\Database\Orm\Interfaces\RelationshipInterface;

/**
 * Class Reference
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Reference implements RelationshipInterface
{

    /**
     * @param string $entity
     * @param string $referencedColumnName
     * @param array  $on
     * @param array  $onOptions
     */
    public function __construct(
        public string $entity,
        public string $referencedColumnName,
        public array  $on = [],
        public array  $onOptions = []
    )
    {
    }

}