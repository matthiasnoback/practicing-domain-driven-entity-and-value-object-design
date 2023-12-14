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
}
