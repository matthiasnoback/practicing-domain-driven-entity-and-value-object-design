<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

class Currency
{
    private string $currency;

    public function __construct(string $currency)
    {
        // @TODO Have a check: is a known currency? Throw exception otherwise
        $this->currency = $currency;
    }

    public function toString(): string
    {
        return $this->currency;
    }
}
