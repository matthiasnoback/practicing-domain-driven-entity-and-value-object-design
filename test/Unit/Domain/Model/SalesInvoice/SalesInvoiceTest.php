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
            1001,
            new DateTimeImmutable(),
            Currency::USD(),
            1.3,
            3
        );

        $salesInvoice->addLine(
            new Line(
                1,
                'Product with a 10% discount and standard VAT applied',
                2.0,
                $salesInvoice->getQuantityPrecision(),
                15.0,
                10.0,
                'S',
                $salesInvoice->getExchangeRate(),
                $salesInvoice->getCurrency()
            )
        );
        $salesInvoice->addLine(
            new Line(
                2,
                'Product with no discount and low VAT applied',
                3.123456,
                $salesInvoice->getQuantityPrecision(),
                12.50,
                null,
                'L',
                $salesInvoice->getExchangeRate(),
                $salesInvoice->getCurrency()
            )
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
            new Line(
                $this->aProductId(),
                'Product with a 10% discount and standard VAT applied',
                2.0,
                $salesInvoice->getQuantityPrecision(),
                15.0,
                10.0,
                'S',
                $salesInvoice->getExchangeRate(),
                $salesInvoice->getCurrency()
            )
        );
        $salesInvoice->addLine(
            new Line(
                $this->anotherProductId(),
                'Product with no discount and low VAT applied',
                3.123456,
                $salesInvoice->getQuantityPrecision(),
                12.50,
                null,
                'L',
                $salesInvoice->getExchangeRate(),
                $salesInvoice->getCurrency()
            )
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
            new Line(
                $this->aProductId(),
                $this->aDescription(),
                $this->aQuantity(),
                $salesInvoice->getQuantityPrecision(),
                $this->aTariff(),
                null,
                'Invalid VAT code',
                $salesInvoice->getExchangeRate(),
                $salesInvoice->getCurrency()
            )
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

    public static function invalidQuantities(): array
    {
        return [
            [-0.5],
            [0.0],
        ];
    }

    /**
     * @dataProvider invalidQuantities
     */
    public function test_add_line_requires_positive_quantity(float $quantity): void
    {
        $salesInvoice = $this->createSalesInvoice();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity');

        $salesInvoice->addLine(
            new Line(
                $this->aProductId(),
                $this->aDescription(),
                $quantity,
                $salesInvoice->getQuantityPrecision(),
                $this->aTariff(),
                null,
                $this->aVatCode(),
                $salesInvoice->getExchangeRate(),
                $salesInvoice->getCurrency()
            )
        );
    }

    /**
     * @return SalesInvoice
     */
    private function createSalesInvoice(): SalesInvoice
    {
        $salesInvoice = SalesInvoice::create(
            1001,
            new DateTimeImmutable(),
            Currency::EUR(),
            null,
            3
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
}
