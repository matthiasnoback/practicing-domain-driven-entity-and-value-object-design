<?php
declare(strict_types=1);

namespace Application;

use Domain\Model\SalesInvoice\SalesInvoice;
use Domain\Model\SalesInvoice\SalesInvoiceRepository;

final class InvoiceService
{
    private SalesInvoiceRepository $salesInvoiceRepository;

    public function __construct(SalesInvoiceRepository $salesInvoiceRepository)
    {
        $this->salesInvoiceRepository = $salesInvoiceRepository;
    }

    public function create($data): void
    {
        $now = $this->clock->currentTime();
        $salesInvoice = SalesInvoice::create(
            $this->salesInvoiceRepository,
            $this->clock,
            $now,
            $data
        );
    }
}
