<?php

namespace Codememory\Components\Database\Orm\Constructions;

use Attribute;

/**
 * Class Repository
 *
 * @package Codememory\Components\Database\Orm\Constructions
 *
 * @author  Codememory
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Repository
{

    /**
     * Repository constructor.
     *
     * @param string $repository
     */
    public function __construct(
        public string $repository,
        public ?string $n = null
    )
    {
    }

}