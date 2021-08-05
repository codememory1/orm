<?php

namespace Codememory\Components\Database\Orm\Interfaces;

use Generator;

/**
 * Interface EntityRepositoryInterface
 *
 * @package Codememory\Components\Database\Orm\Interfaces
 *
 * @author  Codememory
 */
interface EntityRepositoryInterface
{

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns a generator of all records
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return Generator
     */
    public function findAll(): Generator;

}