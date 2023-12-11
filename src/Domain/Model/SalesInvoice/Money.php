<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class Money
{
    private float $amount;
    private Currency $currency;

    public function __construct(float $amount, Currency $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function calculateDiscount(float $percentage): self
    {
        return new self(
            round($this->amount * $percentage / 100, 2),
            $this->currency
        );
    }
}
