<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    public function test_correct_code(): void
    {
        $currencyCode = 'EUR';
        $this->assertSame($currencyCode, Currency::createWith($currencyCode)->toString());
    }
    // @TODO test currencies we can use (EUR, USD); use a data provider
    // @TODO test currencies we can't use (???)
    // @TODO add a function for "if ledger currency"
    // @TODO currency conversion using exchange rate
}
