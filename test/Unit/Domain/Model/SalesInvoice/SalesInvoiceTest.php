<?php

namespace Domain\Model\SalesInvoice;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SalesInvoiceTest extends TestCase
{
    /**
     * @test
     */
    public function it_calculates_the_correct_totals_for_an_invoice_in_foreign_currency(): void
    {
        $salesInvoice = new SalesInvoice();
        $salesInvoice->setCurrency('USD');
        $salesInvoice->setExchangeRate(1.3);
        $salesInvoice->setQuantityPrecision(3);

        $salesInvoice->addLine(
            'Product with a 10% discount and standard VAT applied',
            2.0,
            15.0,
            10.0,
            'S'
        );
        $salesInvoice->addLine(
            'Product with no discount and low VAT applied',
            3.123456,
            12.50,
            null,
            'L'
        );

        /*
         * 2 * 15.00 - 10% = 27.00
         * +
         * 3.123 * 12.50 - 0% = 39.04
         * =
         * 66.04
         */
        self::assertEquals(66.04, $salesInvoice->totalNetAmount());

        /*
         * 66.04 / 1.3 = 50.80
         */
        self::assertEquals(50.80, $salesInvoice->totalNetAmountInLedgerCurrency());

        /*
         * 27.00 * 21% = 5.67
         * +
         * 39.04 * 9% = 3.51
         * =
         * 9.18
         */
        self::assertEquals(9.18, $salesInvoice->totalVatAmount());

        /*
         * 9.18 / 1.3 = 7.06
         */
        self::assertEquals(7.06, $salesInvoice->totalVatAmountInLedgerCurrency());
    }

    /**
     * @test
     */
    public function it_calculates_the_correct_totals_for_an_invoice_in_ledger_currency(): void
    {
        $salesInvoice = $this->createSalesInvoice();
        $salesInvoice->addLine(
            'Product with a 10% discount and standard VAT applied',
            2.0,
            15.0,
            10.0,
            'S'
        );
        $salesInvoice->addLine(
            'Product with no discount and low VAT applied',
            3.123456,
            12.50,
            null,
            'L'
        );

        self::assertEquals($salesInvoice->totalNetAmount(), $salesInvoice->totalNetAmountInLedgerCurrency());
        self::assertEquals($salesInvoice->totalVatAmount(), $salesInvoice->totalVatAmountInLedgerCurrency());
    }

    /**
     * @test
     */
    public function it_fails_when_you_provide_an_unknown_vat_code(): void
    {
        $salesInvoice = $this->createSalesInvoice();

        $this->expectException(InvalidArgumentException::class);

        $salesInvoice->addLine(
            $this->someDescription(),
            $this->someQuantity(),
            $this->someTariff(),
            null,
            'Invalid VAT code'
        );
    }

    /**
     * @return SalesInvoice
     */
    private function createSalesInvoice(): SalesInvoice
    {
        $salesInvoice = new SalesInvoice();
        $salesInvoice->setCurrency('EUR');
        $salesInvoice->setQuantityPrecision(3);

        return $salesInvoice;
    }

    private function someDescription(): string
    {
        return 'Description';
    }

    private function someQuantity(): float
    {
        return 2.0;
    }

    private function someTariff(): float
    {
        return 15.0;
    }
}
