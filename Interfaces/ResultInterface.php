<?php

namespace Codememory\Components\Database\Orm\Interfaces;

use ArrayIterator;
use Generator;

/**
 * Interface ResultInterface
 *
 * @package Codememory\Components\Database\Orm\Interfaces
 *
 * @author  Codememory
 */
interface ResultInterface
{

    /**
     * @return array|object|bool
     */
    public function first(): array|object|bool;

    /**
     * @return array|object|bool
     */
    public function last(): array|object|bool;

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @return ArrayIterator
     */
    public function iterator(): ArrayIterator;

    /**
     * @return Generator
     */
    public function generator(): Generator;

}