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

    public function takePercentage(float $percentage): self
    {
        return new self(round($this->amount * ($percentage / 100), 2));
    }
}
