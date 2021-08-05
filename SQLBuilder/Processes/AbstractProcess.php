<?php

namespace Codememory\Components\Database\Orm\SQLBuilder\Processes;

use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Database\Orm\Interfaces\SQLBuilderProcessInterface;
use Codememory\Components\Database\Orm\SQLBuilder\AlterHelper;
use JetBrains\PhpStorm\Pure;

/**
 * Class AbstractProcess
 *
 * @package Codememory\Components\Database\Orm\SQLBuilder\Processes
 *
 * @author  Codememory
 */
abstract class AbstractProcess implements SQLBuilderProcessInterface
{

    /**
     * @var array
     */
    protected array $entityComparison;

    /**
     * @var AlterHelper
     */
    protected AlterHelper $alterHelper;

    /**
     * @param array               $entityComparison
     * @param EntityDataInterface $entityData
     */
    #[Pure]
    public function __construct(array $entityComparison, EntityDataInterface $entityData)
    {

        $this->entityComparison = $entityComparison;
        $this->alterHelper = new AlterHelper($entityData);

    }

}