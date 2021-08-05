<?php

namespace Codememory\Components\Database\Orm\Interfaces;

use Codememory\Components\Database\Schema\StatementComponents\ReferenceDefinition;

/**
 * Interface RelationshipInterface
 *
 * @package Codememory\Components\Database\Orm\Interfaces
 *
 * @author  Codememory
 */
interface RelationshipInterface
{

    public const ON_DELETE = 'onDelete';
    public const ON_UPDATE = 'onUpdate';
    public const DEFAULT_ON_OPTION = ReferenceDefinition::RD_CASCADE;

}