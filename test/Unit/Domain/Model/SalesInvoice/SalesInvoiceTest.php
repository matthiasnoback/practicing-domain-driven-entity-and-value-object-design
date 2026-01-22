<?php

namespace Domain\Model\SalesInvoice;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SalesInvoiceTest extends TestCase
{
    public function testCreateDraft(): void
    {
        $invoice = SalesInvoice::createDraft(1001, new DateTimeImmutable('2026-01-22'), 'USD', 1.3);
        $this->assertEquals(1001, $invoice->getCustomerId());
        // @TODO and so on
    }

    public function testNonEURCurrencyRequiresExchangeRate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $invoice = SalesInvoice::createDraft(1001, new DateTimeImmutable('2026-01-22'), 'USD', null);
    }

    public function testLineProductShouldBeUnique(): void
    {
        $salesInvoice = SalesInvoice::createDraft(1001, new DateTimeImmutable(), 'USD', 1.3);

        $this->expectException(\InvalidArgumentException::class);
        $sameProductId = 1;
        $salesInvoice->addLine(
            $sameProductId,
            'Product with a 10% discount and standard VAT applied',
            2.0,
            15.0,
            10.0,
            'S'
        );
        $salesInvoice->addLine(
            $sameProductId,
            'Product with a 10% discount and standard VAT applied',
            2.0,
            15.0,
            10.0,
            'S'
        );
    }

    /**
     * @test
     */
    public function it_calculates_the_correct_totals_for_an_invoice_in_foreign_currency(): void
    {
        $salesInvoice = SalesInvoice::createDraft(1001, new DateTimeImmutable(), 'USD', 1.3);

        $salesInvoice->addLine(
            1,
            'Product with a 10% discount and standard VAT applied',
            2.0,
            15.0,
            10.0,
            'S'
        );
        $salesInvoice->addLine(
            2,
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
        self::assertEquals(66.04, $salesInvoice->totalNetAmount()->asFloat());

        /*
         * 66.04 / 1.3 = 50.80
         */
        self::assertTrue($salesInvoice->totalNetAmountInLedgerCurrency()->equals(new Money(50.80, new Currency('EUR'))));

        /*
         * 27.00 * 21% = 5.67
         * +
         * 39.04 * 9% = 3.51
         * =
         * 9.18
         */
        self::assertEquals(9.18, $salesInvoice->totalVatAmount()->asFloat());

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
            10.0,
            'S'
        );
        $salesInvoice->addLine(
            $this->anotherProductId(),
            'Product with no discount and low VAT applied',
            3.123456,
            12.50,
            null,
            'L'
        );

        self::assertEquals($salesInvoice->totalNetAmount(), $salesInvoice->totalNetAmountInLedgerCurrency());
        self::assertEquals($salesInvoice->totalVatAmount()->asFloat(), $salesInvoice->totalVatAmountInLedgerCurrency());
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
            null,
            'Invalid VAT code'
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
        return SalesInvoice::createDraft(1001, new DateTimeImmutable());
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
}
