<?php

namespace Common;

use Common\EventDispatcher\EventDispatcher;

abstract class AggregateRepository
{
    /**
     * @var Aggregate[]
     */
    private $objects;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function store(Aggregate $aggregateRoot): void
    {
        $this->objects[(string)$aggregateRoot->id()] = $aggregateRoot;

        $this->eventDispatcher->dispatchAll($aggregateRoot->recordedEvents());
    }

    protected function load(string $id): ?Aggregate
    {
        if (!isset($this->objects[$id])) {
            return null;
        }

        return $this->objects[$id];
    }
}
