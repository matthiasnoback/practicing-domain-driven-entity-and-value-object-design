<?php
declare(strict_types=1);

namespace Example;

use function Common\CommandLine\line;
use function Common\CommandLine\make_green;
use function Common\CommandLine\stdout;

final class ExampleAggregateCreatedSubscriber
{
    public function __invoke(ExampleAggregateCreated $event)
    {
        stdout(line(make_green('Aggregate created'), 'at', $event->createdAt()->format(DATE_ATOM)));
    }
}
