<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    public function test_unknown_currency(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Currency('WRONG');
    }

    public function test_known_currency(): void
    {
        $currencyString = 'EUR';
        $currency = new Currency($currencyString);
        $this->assertSame($currencyString, $currency->toString());
    }

    public function test_is_ledger_currency(): void
    {
        $currency = new Currency('EUR');
        $this->assertTrue($currency->isLedgerCurrency());
    }

    public function test_is_not_a_ledger_currency(): void
    {
        $currency = new Currency('USD');
        $this->assertFalse($currency->isLedgerCurrency());
    }
}
