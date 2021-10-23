<?php

namespace Codememory\Components\Database\Orm\Interfaces;

use Codememory\Components\Database\Orm\QueryBuilder\Answer\ResultTo;

/**
 * Interface ExtendedQueryBuilderInterface
 *
 * @package Codememory\Components\Database\Orm\Interfaces
 *
 * @author  Codememory
 */
interface ExtendedQueryBuilderInterface
{

    /**
     * @return object
     */
    public function getEntity(): object;

    /**
     * @return EntityDataInterface
     */
    public function getEntityData(): EntityDataInterface;

    /**
     * @return ExtendedQueryBuilderInterface
     */
    public function generateResult(): ExtendedQueryBuilderInterface;

    /**
     * @return ResultTo
     */
    public function to(): ResultTo;

    /**
     * @return ResultTo
     */
    public function generateTo(): ResultTo;

    /**
     * @return bool|array
     */
    public function getGeneratedResult(): bool|array;

}