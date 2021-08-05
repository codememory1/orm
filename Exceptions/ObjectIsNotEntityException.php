<?php

namespace Codememory\Components\Database\Orm\Exceptions;

use ErrorException;
use JetBrains\PhpStorm\Pure;

/**
 * Class ObjectIsNotEntityException
 *
 * @package Codememory\Components\Database\Orm\Exceptions
 *
 * @author  Codememory
 */
class ObjectIsNotEntityException extends ErrorException
{

    /**
     * ObjectIsNotEntityException constructor.
     *
     * @param object $entity
     */
    #[Pure]
    public function __construct(object $entity)
    {

        parent::__construct(sprintf('The %s object is not an entity', $entity::class));

    }

}