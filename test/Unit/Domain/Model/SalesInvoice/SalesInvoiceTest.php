<?php

namespace Domain\Model\SalesInvoice;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SalesInvoiceTest extends TestCase
{
    private int $nextProductId = 1;

    public function testCreateDraft(): void
    {
        $invoice = SalesInvoice::createDraft(1001, new DateTimeImmutable('2026-01-22'), 'USD', 1.3);
        $this->assertEquals(1001, $invoice->getCustomerId());
        // @TODO and so on
    }

    public function testNonEURCurrencyRequiresExchangeRate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $invoice = $this->createDraftInvoice(currency: 'USD');
    }

    public function testLineProductShouldBeUnique(): void
    {
        $salesInvoice = $this->createDraftInvoice();

        $sameProductId = 1;
        $this->addALine($salesInvoice, $sameProductId);

        $this->expectException(\InvalidArgumentException::class);
        $this->addALine($salesInvoice, $sameProductId);
    }

    /**
     * @test
     */
    public function it_calculates_the_correct_totals_for_an_invoice_in_foreign_currency(): void
    {
        $salesInvoice = SalesInvoice::createDraft(1001, new DateTimeImmutable(), 'USD', 1.3);

        $this->addALine($salesInvoice, productId: 1, quantity: 2.0, tariff: 15.0, discount: 10.0,vatCode: 'S');
        $this->addALine($salesInvoice, productId: 2, quantity: 3.123456, tariff: 12.50, discount: null, vatCode: 'L');

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
        $salesInvoice = $this->createDraftInvoice();
        $this->addALine($salesInvoice, quantity: 2.0,
            tariff: 15.0,
            discount: 10.0,
            vatCode: 'S');
        $this->addALine($salesInvoice, quantity: 3.123456,
            tariff: 12.50,
            vatCode: 'L');

        self::assertEquals($salesInvoice->totalNetAmount(), $salesInvoice->totalNetAmountInLedgerCurrency());
        self::assertEquals($salesInvoice->totalVatAmount()->asFloat(), $salesInvoice->totalVatAmountInLedgerCurrency());
    }

    /**
     * @test
     */
    public function it_fails_when_you_provide_an_unknown_vat_code(): void
    {
        $salesInvoice = $this->createSalesInvoiceWithLines();

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
        $salesInvoice = $this->createSalesInvoiceWithLines();
        self::assertFalse($salesInvoice->isFinalized());

        $salesInvoice->finalize();

        self::assertTrue($salesInvoice->isFinalized());
    }

    public function testYouCannotFinalizeTwice(): void
    {
        $salesInvoice = $this->createSalesInvoiceWithLines();
        $salesInvoice->finalize();

        $this->expectException(FinalizeAgainException::class);
        $salesInvoice->finalize();
    }

    public function testYouCannotAddALineToAFinalizeInvoice(): void
    {
        $salesInvoice = $this->createSalesInvoiceWithLines();
        $salesInvoice->finalize();

        $this->expectException(InvalidChangeForLifecycleException::class);
        $this->expectExceptionMessage('finalized');
        $this->addALine($salesInvoice);
    }


    public function testYouCannotFinalizeAnInvoiceWithNoLines(): void
    {
        $salesInvoice = $this->createDraftInvoice();

        $this->expectException(InvalidLifecycleChangeException::class);
        $salesInvoice->finalize();
    }

    public function testYouCannotAddALineToACancelledInvoice(): void
    {
        $salesInvoice = $this->createSalesInvoiceWithLines();
        $salesInvoice->cancel();

        $this->expectException(InvalidChangeForLifecycleException::class);
        $this->expectExceptionMessage('cancelled');

        $this->addALine($salesInvoice);
    }

    /**
     * @test
     */
    public function you_can_cancel_an_invoice(): void
    {
        $salesInvoice = $this->createSalesInvoiceWithLines();
        self::assertFalse($salesInvoice->isCancelled());

        $salesInvoice->cancel();

        self::assertTrue($salesInvoice->isCancelled());
    }
    /**
     * @test
     */
    public function you_can_not_cancel_a_finalized_invoice(): void
    {
        $salesInvoice = $this->createSalesInvoiceWithLines();

        $salesInvoice->finalize();

        $this->expectException(InvalidLifecycleChangeException::class);
        $salesInvoice->cancel();
    }

    /**
     * @test
     */
    public function you_can_not_finalize_a_cancelled_invoice(): void
    {
        $salesInvoice = $this->createSalesInvoiceWithLines();

        $salesInvoice->cancel();

        $this->expectException(InvalidLifecycleChangeException::class);
        $salesInvoice->finalize();
    }

    /**
     * @return SalesInvoice
     */
    private function createSalesInvoiceWithLines(): SalesInvoice
    {
        $salesInvoice = $this->createDraftInvoice();
        $salesInvoice->addLine(
            1,
            $this->aDescription(),
            $this->aQuantity(),
            $this->aTariff(),
            null,
            'L',
        );
        return $salesInvoice;
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
        $productId = $this->nextProductId;
        $this->nextProductId++;
        return $productId;
    }

    private function createDraftInvoice(?string $currency = null, ?float $exchangeRate = null): SalesInvoice
    {
        return SalesInvoice::createDraft(1001, new DateTimeImmutable(), $currency ?? 'EUR', $exchangeRate);
    }

    public function addALine(SalesInvoice $salesInvoice,
                             ?int $productId = null,
                             ?float $quantity = null,
                             ?float $tariff = null,
                             ?float $discount = null,
                             ?string $vatCode = null,
    ): void
    {
        $salesInvoice->addLine(
            $productId ?? $this->aProductId(),
            'Product with a 10% discount and standard VAT applied',
            $quantity ?? $this->aQuantity(),
            $tariff ?? $this->aTariff(),
            $discount,
            $vatCode ?? 'S'
        );
    }
}
