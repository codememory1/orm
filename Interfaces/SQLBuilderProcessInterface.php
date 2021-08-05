<?php

namespace Codememory\Components\Database\Orm\Interfaces;

/**
 * Interface SQLBuilderProcessInterface
 *
 * @package Codememory\Components\Database\Orm\Interfaces
 *
 * @author  Codememory
 */
interface SQLBuilderProcessInterface
{

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Processing process. Callback is called every iteration and is
     * the handler for this iteration
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param callable $handler
     *
     * @return void
     */
    public function process(callable $handler): void;

}