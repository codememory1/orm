<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;
use Codememory\Components\Database\Orm\Interfaces\ColumnComponentInterface;

/**
 * Class DefaultValue
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DefaultValue implements ColumnComponentInterface
{

    /**
     * DefaultValue constructor.
     *
     * @param string $value
     */
    public function __construct(
        public string $value
    )
    {
    }

}