<?php

namespace Codememory\Components\Database\Orm\Interfaces;

use ArrayIterator;
use Codememory\Components\Database\QueryBuilder\Interfaces\QueryResultInterface;
use Generator;

/**
 * Interface ExtendedQueryBuilderInterface
 *
 * @package Codememory\Components\Database\Orm\Interfaces
 *
 * @author  Codememory
 */
interface ExtendedQueryBuilderInterface extends QueryResultInterface
{

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns an array of records as an entity
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param object|null $entity
     * @param bool|array  $records
     *
     * @return array
     */
    public function toEntity(?object $entity = null, bool|array $records = false): array;

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns an iterator of records
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array $records
     *
     * @return ArrayIterator
     */
    public function iterator(array $records): ArrayIterator;

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns the record generator
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array $records
     *
     * @return Generator
     */
    public function generator(array $records): Generator;

}