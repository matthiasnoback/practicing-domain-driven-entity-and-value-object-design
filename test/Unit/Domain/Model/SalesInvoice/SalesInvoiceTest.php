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
        $salesInvoice = SalesInvoice::createDraft(
            $this->aSalesInvoiceId(),
            1001,
            new DateTimeImmutable(),
            Currency::createWith('USD'),
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

    /**
     * @test
     */
    public function you_can_cancel_an_invoice(): void
    {
        $salesInvoice = $this->createSalesInvoice();
        self::assertFalse($salesInvoice->isCancelled());

        $salesInvoice->cancel();

        self::assertTrue($salesInvoice->isCancelled());
    }

    public function test_you_cannot_cancel_an_invoice_when_it_is_finalized(): void
    {
        $salesInvoice = $this->aFinalizedInvoice();

        $this->expectException(\LogicException::class);

        $salesInvoice->cancel();
    }

    public function test_you_cannot_add_a_line_to_a_finalized_invoice(): void
    {
        $this->expectException(\LogicException::class);
        $this->addLine($this->aFinalizedInvoice());
    }

    public function test_you_cannot_add_a_line_to_a_cancelled_invoice(): void
    {
        $this->expectException(\LogicException::class);
        $this->addLine($this->aCancelledInvoice());
    }

    public function test_exchange_rate_is_mandatory_when_currency_is_not_the_ledger_currency(): void
    {
        // ExchangeRate: has two currencies, they could be the same EUR-EUR, EUR-USD
        // Conversion
        // Currency: has an exchange rate

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exchange rate');

        // @TODO hide the UUID part from the constructor
        // @TODO remove duplication
        SalesInvoice::createDraft(
            $this->aSalesInvoiceId(),
            1001,
            new DateTimeImmutable(),
            Currency::createWith('USD'),
            null,
            3
        );
    }

    public function test_line_quantity_should_be_more_than_0(): void
    {
        $salesInvoice = $this->createSalesInvoice();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('quantity');

        $this->addLine($salesInvoice, null, 0.0);
    }

    public function test_same_product_cannot_be_added_twice_to_an_invoice(): void
    {
        $salesInvoice = $this->createSalesInvoice();

        $sameProduct = $this->aProductId();

        $this->addLine($salesInvoice, $sameProduct);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product');

        $this->addLine($salesInvoice, $sameProduct);
    }

    public function test_cannot_finalize_a_cancelled_invoice(): void
    {
        $cancelledInvoice = $this->createSalesInvoice();
        $cancelledInvoice->cancel();

        $this->expectException(\LogicException::class);

        $cancelledInvoice->finalize();
    }

    /**
     * @return SalesInvoice
     */
    private function createSalesInvoice(): SalesInvoice
    {
        return SalesInvoice::createDraft(
            $this->aSalesInvoiceId(),
            1001,
            new DateTimeImmutable(),
            Currency::createWith('EUR'),
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

    public function addLine(SalesInvoice $salesInvoice, ?int $productId = null, ?float $quantity = null): void
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

    /**
     * @return SalesInvoice
     */
    public function aFinalizedInvoice(): SalesInvoice
    {
        $salesInvoice = $this->createSalesInvoice();
        $salesInvoice->finalize();
        return $salesInvoice;
    }

    private function aCancelledInvoice(): SalesInvoice
    {
        $salesInvoice = $this->createSalesInvoice();
        $salesInvoice->cancel();

        return $salesInvoice;
    }

    /**
     * @return SalesInvoiceId
     */
    public function aSalesInvoiceId(): SalesInvoiceId
    {
        return SalesInvoiceId::fromUuid(
            'f0b8eff6-d6f5-4fca-b976-6ea061cb32b7'
        );
    }
}
