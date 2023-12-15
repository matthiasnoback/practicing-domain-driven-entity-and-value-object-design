<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    private static function amount(float $amount): Money
    {
        return Money::fromFloat($amount, Currency::createWith('EUR'));
    }

    public function test_to_float(): void
    {
        $this->assertSame(2.50, self::amount(2.50)->toFloat());
    }

    public function test_no_discount_amount(): void
    {
        $this->assertSame(0.00, (new Money(2.50, Currency::createWith('EUR')))->takePercentage(0.0)->toFloat());
    }

    public function test_discount_amount(): void
    {
        $this->assertSame(1.25, (new Money(2.50, Currency::createWith('EUR')))->takePercentage(50.0)->toFloat());
    }

    public function test_subtract_amount(): void
    {
        $this->assertTrue(
            self::amount(10.0)->equals(
                self::amount(30.0)->subtract(self::amount(20.0))
            )
        );
    }

    public function test_equality(): void
    {
        $this->assertTrue(self::amount(10.0)->equals(self::amount(10.0)));
    }

    public function test_inequality(): void
    {
        $this->assertFalse(self::amount(5.0)->equals(self::amount(10.0)));
    }

    public function test_it_rounds(): void
    {
        $this->assertTrue(
            self::amount(1.1234)->equals(
                self::amount(1.12)
            )
        );
    }
}
