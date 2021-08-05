<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;

/**
 * Class Entity
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Entity
{

    /**
     * Entity constructor.
     *
     * @param string $tableName
     */
    public function __construct(
        public string $tableName
    )
    {
    }

}