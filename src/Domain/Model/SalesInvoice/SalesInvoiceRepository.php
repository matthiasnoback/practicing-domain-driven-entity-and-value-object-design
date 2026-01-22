<?php

namespace Domain\Model\SalesInvoice;

interface SalesInvoiceRepository
{
    public function nextIdentity(): SalesInvoiceId;
}
