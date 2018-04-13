<?php
declare(strict_types=1);

namespace Example;

use DateTimeImmutable;

final class ExampleAggregateCreated
{
    private $createdAt;

    private $exampleAggregateId;

    public function __construct(ExampleAggregateId $exampleAggregateId, DateTimeImmutable $createdAt)
    {
        $this->exampleAggregateId = $exampleAggregateId;
        $this->createdAt = $createdAt;
    }

    public function exampleAggregateId(): ExampleAggregateId
    {
        return $this->exampleAggregateId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
