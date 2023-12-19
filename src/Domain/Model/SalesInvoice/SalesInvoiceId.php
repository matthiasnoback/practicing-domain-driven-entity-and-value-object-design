<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;

final readonly class SalesInvoiceId
{
    public function __construct(private string $uuid)
    {
        Assertion::uuid($uuid);
    }

    public function toString(): string
    {
        return $this->uuid;
    }
}
// @TODO create a service for retrieving a new ID
