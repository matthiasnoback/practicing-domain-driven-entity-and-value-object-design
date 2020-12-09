<?php

namespace Domain\Model\SalesInvoice;

use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SalesInvoiceTest extends TestCase
{
    /**
     * @test
     */
    public function it_calculates_the_correct_totals_for_an_invoice_in_foreign_currency(): void
    {
        $salesInvoice = new SalesInvoice(
            1001,
            new DateTimeImmutable(),
            'USD',
            1.3,
            3
        );

        $salesInvoice->addLine(
            1,
            'Product with a 10% discount and standard VAT applied',
            2.0,
            15.0,
            Discount::fromFloatPercentage(10.0),
            'S',
            $this->vatRate('S')
        );
        $salesInvoice->addLine(
            2,
            'Product with no discount and low VAT applied',
            3.123456,
            12.50,
            Discount::noDiscount(),
            'L',
            $this->vatRate('L')
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
            $this->aProductId(),
            'Product with a 10% discount and standard VAT applied',
            2.0,
            15.0,
            Discount::fromFloatPercentage(10.0),
            'S',
            $this->vatRate('S')
        );
        $salesInvoice->addLine(
            $this->anotherProductId(),
            'Product with no discount and low VAT applied',
            3.123456,
            12.50,
            Discount::noDiscount(),
            'L',
            $this->vatRate('L')
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
            $this->aProductId(),
            $this->aDescription(),
            $this->aQuantity(),
            $this->aTariff(),
            Discount::noDiscount(),
            'Invalid VAT code',
            $this->vatRate('Invalid VAT code')
        );
    }

    /**
     * @test
     */
    public function you_have_to_provide_an_exchange_rate_if_the_currency_is_not_eur(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exchange rate');

        new SalesInvoice(
            1001,
            new DateTimeImmutable(),
            'USD',
            null,
            3
        );
    }

    /**
     * @test
     */
    public function you_can_finalize_an_invoice(): void
    {
        $salesInvoice = $this->createSalesInvoice();
        self::assertFalse($salesInvoice->isFinalized());

        $salesInvoice->setFinalized(true);

        self::assertTrue($salesInvoice->isFinalized());
    }

    /**
     * @test
     */
    public function you_can_cancel_an_invoice(): void
    {
        $salesInvoice = $this->createSalesInvoice();
        self::assertFalse($salesInvoice->isCancelled());

        $salesInvoice->setCancelled(true);

        self::assertTrue($salesInvoice->isCancelled());
    }

    /**
     * @return SalesInvoice
     */
    private function createSalesInvoice(): SalesInvoice
    {
        return new SalesInvoice(
            1001,
            new DateTimeImmutable(),
            'EUR',
            null,
            3
        );
    }

    private function aDescription(): string
    {
        return 'Description';
    }

    private function aQuantity(): float
    {
        return 2.0;
    }

    private function aTariff(): float
    {
        return 15.0;
    }

    private function aProductId(): int
    {
        return 1;
    }

    private function anotherProductId(): int
    {
        return 2;
    }

    private function vatRate(string $vatCode): float
    {
        return (new VatRates())->vatRateForVatCodeAtDate(new DateTime(), $vatCode);
    }
}
