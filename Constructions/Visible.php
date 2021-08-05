<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;
use Codememory\Components\Database\Orm\Interfaces\ColumnComponentInterface;

/**
 * Class Visible
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Visible implements ColumnComponentInterface
{

    /**
     * Visible constructor.
     *
     * @param bool $visible
     */
    public function __construct(
        public bool $visible = true
    )
    {
    }

}