<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class VatRate
{
    private string $code;
    private float $rateAsPercentage;

    public function __construct(string $code, float $rateAsPercentage)
    {
        $this->code = $code;
        $this->rateAsPercentage = $rateAsPercentage;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function rateAsPercentage(): float
    {
        return $this->rateAsPercentage;
    }
}
