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
            ['USD'],
            ['EUR'],
        ];
    }

    /**
     * @dataProvider correctCurrencies
     */
    public function test_correct_code(string $currencyCode): void
    {
        $this->assertSame($currencyCode, Currency::createWith($currencyCode)->toString());
    }

    public function test_invalid_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Currency::createWith('BTC');
    }
    // @TODO add a function for "if ledger currency"
    // @TODO currency conversion using exchange rate
}
