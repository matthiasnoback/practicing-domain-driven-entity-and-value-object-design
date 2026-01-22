<?php

namespace Domain\Model\SalesInvoice;

final class SalesInvoiceId
{
    private string $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }
}
