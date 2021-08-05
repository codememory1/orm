<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Codememory\Components\Database\Schema\StatementComponents\ReferenceDefinition;

/**
 * Class AbstractRelation
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
abstract class AbstractRelation
{

    public const ON_DELETE = 'onDelete';
    public const ON_UPDATE = 'onUpdate';
    public const DEFAULT_ON_OPTION = ReferenceDefinition::RD_CASCADE;

}