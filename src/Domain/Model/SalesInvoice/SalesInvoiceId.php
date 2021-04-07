<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assert;

final class SalesInvoiceId
{
    private string $uuid;

    private function __construct(string $uuid)
    {
        Assert::that($uuid)->uuid();
        $this->uuid = $uuid;
    }

    public static function fromString(string $uuid): self
    {
        return new self($uuid);
    }
}
