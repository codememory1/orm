<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;

/**
 * Class Join
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Join
{

    /**
     * Join constructor.
     *
     * @param string $entity
     * @param array  $columns
     * @param array  $as
     */
    public function __construct(
        public string $entity,
        public array $columns,
        public array $as
    )
    {
    }

}