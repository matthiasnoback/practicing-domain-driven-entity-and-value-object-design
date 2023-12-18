<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_type_equivalence(): void
    {
        $money = new Money(100.0, Currency::EUR());
        $this->assertEquals(100.0, $money->amount());
        $this->assertTrue($money->currency()->equals(Currency::EUR()));
    }

    public function test_money_has_2_decimals(): void
    {
        $tooManyDecimals = new Money(100.1234, Currency::EUR());
        $this->assertEquals(100.12, $tooManyDecimals->amount());
    }

    public function test_calculate_discount(): void
    {
        $money = new Money(200.0, Currency::EUR());
        $discountAmount = $money->discount(10.0);
        self::assertEquals(20.0, $discountAmount->amount());
        // code smell, let's compare Money objects instead
    }

    public function test_calculate_no_discount(): void
    {
        $money = new Money(200.0, Currency::EUR());
        $discountAmount = $money->discount(null);
        self::assertEquals(0.0, $discountAmount->amount());
    }

    public function test_subtract_money(): void
    {
        $money1 = new Money(200.0, Currency::EUR());
        $money2 = new Money(50.0, Currency::EUR());
        self::assertEquals(new Money(150.0, Currency::EUR()), $money1->subtract($money2));
    }

    public function test_subtract_requires_same_currency(): void
    {
        $money1 = new Money(200.0, Currency::EUR());
        $money2 = new Money(50.0, new Currency('USD'));

        $this->expectException(\InvalidArgumentException::class);

        $money1->subtract($money2);
    }
}
