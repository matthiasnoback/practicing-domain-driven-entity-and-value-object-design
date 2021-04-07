<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class Discount
{
    private float $percentage;

    public function __construct(float $percentage)
    {
        $this->percentage = $percentage;
    }

    public function calculateFor(float $amount): float
    {
        return round($amount * $this->percentage / 100, 2);
    }
}
