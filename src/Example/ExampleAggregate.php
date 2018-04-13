<?php
declare(strict_types=1);

namespace Example;

use Common\AggregateId;
use Common\Aggregate;
use DateTimeImmutable;

final class ExampleAggregate extends Aggregate
{
    private $exampleAggregateId;

    public function id(): AggregateId
    {
        return $this->exampleAggregateId;
    }

    public function __construct(ExampleAggregateId $exampleAggregateId)
    {
        $this->exampleAggregateId = $exampleAggregateId;

        $this->recordThat(
            new ExampleAggregateCreated($exampleAggregateId, new DateTimeImmutable())
        );
    }
}
