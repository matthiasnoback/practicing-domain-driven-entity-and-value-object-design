<?php

namespace Domain\Model\SalesInvoice;

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
        $salesInvoice = SalesInvoice::create(
            $this->aSalesInvoiceId(),
            1001,
            new DateTimeImmutable(),
            new Currency('USD'),
            1.3,
            3
        );

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

        $salesInvoice->finalize();

        self::assertTrue($salesInvoice->isFinalized());
    }

    public function test_you_cannot_finalize_an_invoice_that_is_already_finalized(): void
    {
        $salesInvoice = $this->createSalesInvoice();
        $salesInvoice->finalize();

        $this->expectException(InvoiceAlreadyFinalized::class);

        $salesInvoice->finalize();
    }

    public function test_you_cannot_add_a_line_to_an_invoice_that_is_already_finalized(): void
    {
        $salesInvoice = $this->createSalesInvoice();
        $salesInvoice->finalize();

        $this->expectException(InvoiceAlreadyFinalized::class);

        $this->addLine($salesInvoice);
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

    public function test_exchange_rate_should_be_more_than_0(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SalesInvoice::create(
            $this->aSalesInvoiceId(),
            1001,
            new DateTimeImmutable(),
            new Currency('USD'),
            0.0,
            3
        );
    }

    public function test_exchange_rate_should_be_provided_when_currency_is_not_EUR(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SalesInvoice::create(
            $this->aSalesInvoiceId(),
            1001,
            new DateTimeImmutable(),
            new Currency('USD'),
            null,
            3
        );
    }

    public function test_exchange_rate_should_be_not_provided_when_currency_is_EUR(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SalesInvoice::create(
            $this->aSalesInvoiceId(),
            1001,
            new DateTimeImmutable(),
            new Currency('EUR'),
            0.8,
            3
        );
    }

    public function test_you_cannot_add_two_lines_for_the_same_product(): void
    {
        $salesInvoice = $this->createSalesInvoice();

        $sameProductId = $this->aProductId();

        $this->addLine($salesInvoice, $sameProductId);

        $this->expectException(DuplicateProductException::class);

        $this->addLine($salesInvoice, $sameProductId);
    }

    public function test_quantity_should_be_more_than_0(): void
    {
        $salesInvoice = $this->createSalesInvoice();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('quantity');

        $this->addLine(
            $salesInvoice,
            null,
            0.0,
        );
    }

    /**
     * @return SalesInvoice
     */
    private function createSalesInvoice(): SalesInvoice
    {
        return SalesInvoice::create(
            $this->aSalesInvoiceId(),
            1001,
            new DateTimeImmutable(),
            new Currency('EUR'),
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

    private function aVatCode(): string
    {
        return 'S';
    }

    public function addLine(
        SalesInvoice $salesInvoice,
        ?int         $productId = null,
        ?float       $quantity = null,
    ): void
    {
        $salesInvoice->addLine(
            $productId ?? $this->aProductId(),
            $this->aDescription(),
            $quantity ?? $this->aQuantity(),
            $this->aTariff(),
            null,
            $this->aVatCode()
        );
    }

    public function aSalesInvoiceId(): SalesInvoiceId
    {
        return new SalesInvoiceId('e366422d-9454-4b5c-8970-06a541e19184');
    }
}
