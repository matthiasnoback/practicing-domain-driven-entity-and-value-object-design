<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assert;

class Currency
{
    private const CURRENCY_CODES = ['EUR', 'USD'];

    private string $currencyCode;

    private function __construct(string $currencyCode)
    {
        Assert::that($currencyCode)->inArray(self::CURRENCY_CODES);

        $this->currencyCode = $currencyCode;
    }

    public static function createWith(string $currencyCode): self
    {
        return new self($currencyCode);
    }

    public function toString(): string
    {
        return $this->currencyCode;
    }
}
