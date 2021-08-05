<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;

/**
 * Class Column
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Column
{

    /**
     * Column constructor.
     *
     * @param string   $name
     * @param string   $type
     * @param int|null $length
     * @param bool     $nullable
     */
    public function __construct(
        public string $name,
        public string $type,
        public ?int   $length = null,
        public bool   $nullable = false
    )
    {
    }

}