<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use DateTime;
use InvalidArgumentException;

final class VatRates
{
    public function vatRateForVatCodeAtDate(DateTime $now, string $vatCode): float
    {
        if ($vatCode === 'S') {
            return 21.0;
        } elseif ($vatCode === 'L') {
            if ($now < DateTime::createFromFormat('Y-m-d', '2019-01-01')) {
                return 6.0;
            } else {
                return 9.0;
            }
        }

        throw new InvalidArgumentException('Could not determine the VAT rate');
    }
}
