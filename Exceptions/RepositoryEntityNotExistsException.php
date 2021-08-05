<?php

namespace Codememory\Components\Database\Orm\Exceptions;

use ErrorException;
use JetBrains\PhpStorm\Pure;

/**
 * Class RepositoryEntityNotExistsException
 *
 * @package Codememory\Components\Database\Orm\Exceptions
 *
 * @author  Codememory
 */
class RepositoryEntityNotExistsException extends ErrorException
{

    /**
     * RepositoryEntityNotExistsException constructor.
     *
     * @param string $entity
     */
    #[Pure]
    public function __construct(string $entity)
    {

        parent::__construct(sprintf('No repository exists for entity %s', $entity));

    }

}