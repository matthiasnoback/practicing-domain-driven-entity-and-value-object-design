<?php

namespace Domain\Model\SalesInvoice;

class Money
{
    private float $amount;
    private Currency $currency;

    public function __construct(float $amount, Currency $currency) {
        $this->amount = round($amount, 2); // @TODO get precision from Currency
        $this->currency = $currency;
    }

    public function asFloat(): float
    {
        return $this->amount;
    }
}
