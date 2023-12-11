<?php

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_calculate_discount_amount(): void
    {
        $money = new Money(100.00, new Currency('EUR'));

        $this->assertSame(
            23.0,
            $money->calculateDiscount(23.0)->getAmount()
        );
    }
}
