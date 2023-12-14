<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_to_float(): void
    {
        $this->assertSame(2.50, (new Money(2.50))->toFloat());
    }

    public function test_no_discount_amount(): void
    {
        $this->assertSame(0.00, (new Money(2.50))->takePercentage(0.0)->toFloat());
    }

    public function test_discount_amount(): void
    {
        $this->assertSame(1.25, (new Money(2.50))->takePercentage(50.0)->toFloat());
    }

    public function test_subtract_amount(): void
    {
        $this->assertTrue(
            Money::fromFloat(10.0)->equals(
                Money::fromFloat(30.0)->subtract(Money::fromFloat(20.0))
            )
        );
    }

    public function test_equality(): void
    {
        $this->assertTrue((new Money(10.0))->equals(new Money(10.0)));
    }

    public function test_inequality(): void
    {
        $this->assertFalse((new Money(5.0))->equals(new Money(10.0)));
    }

    public function test_it_rounds(): void
    {
        $this->assertTrue(
            Money::fromFloat(1.1234)->equals(
                Money::fromFloat(1.12)
            )
        );
    }
}
