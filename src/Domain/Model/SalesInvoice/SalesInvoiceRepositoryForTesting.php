<?php

namespace Domain\Model\SalesInvoice;

use Ramsey\Uuid\Uuid;

class SalesInvoiceRepositoryForTesting implements SalesInvoiceRepository
{
    public function nextIdentity(): SalesInvoiceId
    {
        return new SalesInvoiceId(Uuid::uuid4()->toString());
    }
}
