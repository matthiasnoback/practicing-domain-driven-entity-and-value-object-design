<?php
declare(strict_types=1);

namespace Common;

use RuntimeException;

final class AggregateNotFound extends RuntimeException
{
    public static function with(string $type, string $id)
    {
        return new self(sprintf(
            'Could not find aggregate of type %s with ID %s',
            $type,
            $id
        ));
    }
}
