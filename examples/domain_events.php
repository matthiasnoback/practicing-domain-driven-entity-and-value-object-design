<?php
declare(strict_types=1);

namespace Example;

use Common\EventDispatcher\EventCliLogger;
use Common\EventDispatcher\EventDispatcher;
use Ramsey\Uuid\Uuid;

require __DIR__ . '/../bootstrap.php';

$eventDispatcher = new EventDispatcher();
$eventDispatcher->subscribeToAllEvents(new EventCliLogger());
$eventDispatcher->registerSubscriber(ExampleAggregateCreated::class, new ExampleAggregateCreatedSubscriber());

$exampleAggregateRepository = new ExampleAggregateRepository($eventDispatcher);

$aggregate = new ExampleAggregate(
    ExampleAggregateId::fromString(
        Uuid::uuid4()->toString()
    )
);

$exampleAggregateRepository->save($aggregate);
