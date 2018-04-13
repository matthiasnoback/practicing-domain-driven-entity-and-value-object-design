<?php
declare(strict_types=1);

namespace Example;

use Common\AggregateNotFound;
use Common\AggregateRepository;
use Common\InMemoryObjectStore;

final class ExampleAggregateRepository extends AggregateRepository
{
    use InMemoryObjectStore;

    public function save(ExampleAggregate $aggregate): void
    {
        $this->store($aggregate);
    }

    public function getById(ExampleAggregateId $exampleAggregateId): ExampleAggregate
    {
        $aggregate = $this->load((string)$exampleAggregateId);

        if (!$aggregate instanceof ExampleAggregate) {
            throw AggregateNotFound::with(ExampleAggregate::class, (string)$exampleAggregateId);
        }

        return $aggregate;
    }
}
