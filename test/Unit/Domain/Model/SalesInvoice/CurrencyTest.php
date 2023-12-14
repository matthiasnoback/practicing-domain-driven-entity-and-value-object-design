<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    /**
     * @return array<array<string>>
     */
    public static function correctCurrencies(): array
    {
        return [
            'USD' => ['USD'],
            'EUR' => ['EUR'],
        ];
    }

    /**
     * @dataProvider correctCurrencies
     */
    public function test_correct_code(string $currencyCode): void
    {
        $this->assertSame($currencyCode, Currency::createWith($currencyCode)->toString());
    }

    public function test_unsupported_codes_are_forbidden(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Currency::createWith('BTC');
    }

    public function test_EUR_is_ledger_currency(): void
    {
        $this->assertTrue(Currency::createWith('EUR')->isLedgerCurrency());
    }

    public function test_other_currencies_are_not_ledger_currency(): void
    {
        $this->assertFalse(Currency::createWith('USD')->isLedgerCurrency());
    }

    // @TODO currency conversion using exchange rate
}
