<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class SalesInvoiceId
{
    private string $uuid;

    private function __construct(string $uuid)
    {
        Assertion::uuid($uuid);

        $this->uuid = $uuid;
    }

    public static function fromUuid(string $uuid): self
    {
        return new self($uuid);
    }

    public function toString(): string
    {
        return $this->uuid;
    }
}
