<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class Currency
{
    private string $currency;

    public function __construct(string $currency)
    {
        if (!in_array($currency, ['EUR', 'USD'])) {
            throw new \InvalidArgumentException('Unknown currency: ' . $currency);
        }

        $this->currency = $currency;
    }

    public function toString(): string
    {
        return $this->currency;
    }

    public function equals(Currency $other): bool
    {
        return $other->currency === $this->currency;
    }

    public function isLedgerCurrency(): bool
    {
        return $this->equals(new self('EUR'));
    }

    public static function EUR(): self
    {
        return new self('EUR');
    }
}
