<?php

namespace Domain\Model\SalesInvoice;

use LogicException;
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

    public function test_subtract(): void
    {
        $money1 = new Money(100.00, new Currency('EUR'));
        $money2 = new Money(40.00, new Currency('EUR'));

        $this->assertSame(
            60.0,
            $money1->subtract($money2)->getAmount()
        );
    }

    public function test_subtracting_non_matching_currencies(): void
    {
        $money1 = new Money(100.00, new Currency('EUR'));
        $money2 = new Money(40.00, new Currency('USD'));

        $this->expectException(LogicException::class);

        $money1->subtract($money2)->getAmount();
    }
}
