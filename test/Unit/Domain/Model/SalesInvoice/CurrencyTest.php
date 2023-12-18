<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    public function test_invalid_currency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Currency('another thing');
    }

    public function test_to_string(): void
    {
        $string = 'EUR';
        self::assertEquals($string, (new Currency($string))->toString());
    }

    public function test_to_string_2(): void
    {
        $string = 'USD';
        self::assertEquals($string, (new Currency($string))->toString());
    }

    public function test_two_unequal_currencies(): void
    {
        $currency = new Currency('EUR');
        self::assertFalse($currency->equals(new Currency('USD')));
    }

    public function test_two_equal_currencies(): void
    {
        $currency = new Currency('EUR');
        self::assertTrue($currency->equals(new Currency('EUR')));
    }
}
