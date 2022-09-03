<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

interface SalesInvoiceRepository
{
    /**
     * Generates a unique ID
     */
    public function nextIdentity(): SalesInvoiceId;
}
