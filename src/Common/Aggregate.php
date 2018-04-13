<?php
declare(strict_types=1);

namespace Common;

use Assert\Assertion;

abstract class Aggregate
{
    private $events = [];

    protected function recordThat($event): void
    {
        Assertion::isObject($event, 'A domain event should be an object.');

        $this->events[] = $event;
    }

    abstract public function id(): AggregateId;

    public function recordedEvents(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }
}
