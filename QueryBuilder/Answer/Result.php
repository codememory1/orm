<?php

namespace Codememory\Components\Database\Orm\QueryBuilder\Answer;

use ArrayIterator;
use Codememory\Components\Database\Orm\Interfaces\ResultInterface;
use Generator;

/**
 * Class Result
 *
 * @package Codememory\Components\Database\Orm\QueryBuilder\Answer
 *
 * @author  Codememory
 */
class Result implements ResultInterface
{

    /**
     * @var array
     */
    private array $records;

    /**
     * @param array $records
     */
    public function __construct(array $records)
    {

        $this->records = $records;

    }

    /**
     * @inheritDoc
     */
    public function first(): array|object|bool
    {

        $key = array_key_first($this->records);

        if ([] !== $this->records) {
            return $this->records[$key];
        }

        return false;

    }

    /**
     * @inheritDoc
     */
    public function last(): array|object|bool
    {

        $key = array_key_last($this->records);

        if ([] !== $this->records) {
            return $this->records[$key];
        }

        return false;

    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {

        return $this->records;

    }

    /**
     * @inheritDoc
     */
    public function iterator(): ArrayIterator
    {

        return new ArrayIterator($this->records);

    }

    /**
     * @inheritDoc
     */
    public function generator(): Generator
    {

        foreach ($this->records as $index => $record) {
            yield $index => $record;
        }

    }

}