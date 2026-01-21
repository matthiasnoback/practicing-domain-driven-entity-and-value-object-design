<?php

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;

final class Currency
{
    private string $currency;

    private array $possibleValues = ['EUR', 'USD', 'GBP'];

    public function __construct(string $currency) {
        Assertion::inArray($currency, $this->possibleValues);
        $this->currency = $currency;
    }

    public function __toString(): string
    {
        return $this->currency;
    }

    public function equals(Currency $other): bool
    {
        return $this->currency === $other->currency;
    }
}
