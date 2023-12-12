<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use InvalidArgumentException;

class Currency
{
    private const CURRENCIES = ['EUR', 'USD'];

    private string $currency;

    public function __construct(string $currency)
    {
        if (! in_array($currency, self::CURRENCIES)) {
            throw new InvalidArgumentException('Unknown currency: ' . $currency);
        }

        $this->currency = $currency;
    }

    public static function EUR(): self
    {
        // keep a "cache", return if already created
        // "memoization"
        return new self('EUR');
    }

    public static function USD(): self
    {
        return new self('USD');
    }

    public function toString(): string
    {
        return $this->currency;
    }

    public function isLedgerCurrency(): bool
    {
        return $this->currency === 'EUR';
    }

    public function isSameAs(Currency $other): bool
    {
        return $this->currency === $other->currency;
    }
}
