<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;

final class ExchangeRate
{
    private float $exchangeRate;
    private Currency $fromCurrency;
    private Currency $toCurrency;

    public function __construct(float $exchangeRate, Currency $fromCurrency, Currency $toCurrency)
    {
        Assertion::greaterThan($exchangeRate, 0.0);
        $this->exchangeRate = $exchangeRate;
        $this->fromCurrency = $fromCurrency;
        $this->toCurrency = $toCurrency;
    }

    public function convert(Money $from): Money
    {
        Assertion::true($from->currency()->equals($this->fromCurrency));

        return new Money($from->asFloat() / $this->exchangeRate, $this->toCurrency);
    }

    public function rateAsFloat(): float
    {
        return $this->exchangeRate;
    }
}
