<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;
use Codememory\Components\Database\Orm\Interfaces\ColumnComponentInterface;

/**
 * Class Collate
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class Collate implements ColumnComponentInterface
{

    /**
     * Collate constructor.
     *
     * @param string $collate
     */
    public function __construct(
        public string $collate
    )
    {
    }

}