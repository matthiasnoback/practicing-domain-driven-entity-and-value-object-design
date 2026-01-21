<?php

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    public function testInvalidCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $currency = new Currency('DMK');
    }
}
