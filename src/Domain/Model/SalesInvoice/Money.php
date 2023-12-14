<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class Money
{
    private float $amount;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    public function toFloat(): float
    {
        return $this->amount;
    }
}
