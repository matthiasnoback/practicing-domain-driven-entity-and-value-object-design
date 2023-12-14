<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

class Currency
{
    private string $currencyCode;

    private function __construct(string $currencyCode)
    {
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
