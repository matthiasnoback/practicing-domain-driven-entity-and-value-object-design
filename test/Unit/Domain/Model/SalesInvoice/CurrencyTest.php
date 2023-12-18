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
        $currency = Currency::EUR();
        self::assertFalse($currency->equals(new Currency('USD')));
    }

    public function test_two_equal_currencies(): void
    {
        $currency = Currency::EUR();
        self::assertTrue($currency->equals(Currency::EUR()));
    }

    public function test_EUR_is_the_ledger_currency(): void
    {
        self::assertFalse((new Currency('USD'))->isLedgerCurrency());
        self::assertTrue((Currency::EUR())->isLedgerCurrency());
    }

    public function test_EUR_const(): void
    {
        self::assertTrue(Currency::EUR()->equals(new Currency('EUR')));
    }
}
