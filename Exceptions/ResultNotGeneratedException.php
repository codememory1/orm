<?php

namespace Codememory\Components\Database\Orm\Exceptions;

use ErrorException;
use JetBrains\PhpStorm\Pure;

/**
 * Class ResultNotGeneratedException
 *
 * @package Codememory\Components\Database\Orm\Exceptions
 *
 * @author  Codememory
 */
class ResultNotGeneratedException extends ErrorException
{

    #[Pure]
    public function __construct()
    {

        parent::__construct('To get the result, you need to generate the result');

    }

}