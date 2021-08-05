<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;
use Codememory\Components\Database\Orm\Interfaces\ColumnComponentInterface;

/**
 * Class Unique
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Unique implements ColumnComponentInterface
{

}