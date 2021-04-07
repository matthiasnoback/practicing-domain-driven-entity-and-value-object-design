<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use DateTimeImmutable;
use InvalidArgumentException;

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

    public function calculateVatForAmount(float $amount): float
    {
        return round(
            $amount * ($this->rateAsPercentage() / 100),
            2
        );
    }

    public static function forCodeAndDate(
        string $code,
        DateTimeImmutable $date
    ): self {
        if ($code === 'S') {
            return new VatRate($code, 21.0);
        }

        if ($code === 'L') {
            if ($date < DateTimeImmutable::createFromFormat('Y-m-d', '2019-01-01')) {
                return new VatRate($code, 6.0);
            }

            return new VatRate($code, 9.0);
        }

        throw new InvalidArgumentException(
            sprintf('Unknown VAT code "%s"', $code)
        );
    }
}
