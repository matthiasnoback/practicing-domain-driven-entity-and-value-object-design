<?php

namespace Domain\Model\SalesInvoice;

use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class SalesInvoiceTest extends TestCase
{
    /**
     * @test
     */
    public function it_calculates_the_correct_totals_for_an_invoice_in_foreign_currency(): void
    {
        $salesInvoice = SalesInvoice::create(
            SalesInvoiceId::fromString(Uuid::uuid4()->toString()),
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

    /**
     * @test
     */
    public function a_foreign_currency_requires_an_exchange_rate_to_be_provided(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('exchange rate');

        SalesInvoice::create(
            SalesInvoiceId::fromString(Uuid::uuid4()->toString()),
            $this->aCustomerId(),
            $this->aDate(),
            'USD', // a foreign currency
            null, // but no exchange rate
            $this->aQuantityPrecision()
        );
    }

    /**
     * @test
     */
    public function you_can_not_add_the_same_product_twice(): void
    {
        $salesInvoice = SalesInvoice::create(
            SalesInvoiceId::fromString(Uuid::uuid4()->toString()),
            $this->aCustomerId(),
            $this->aDate(),
            'EUR',
            null, // but no exchange rate
            $this->aQuantityPrecision()
        );

        $salesInvoice->addLine(1, '', 1.0, 2.0, null, 'S');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('product');

        $salesInvoice->addLine(1, '', 1.0, 2.0, null, 'S');
    }

    /**
     * @test
     */
    public function you_can_not_cancel_a_finalized_invoice(): void
    {
        $finalizedInvoice = $this->createSalesInvoice();
        $finalizedInvoice->finalize();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('finalized');

        $finalizedInvoice->cancel();
    }

    /**
     * @test
     */
    public function you_can_not_finalize_a_cancelled_invoice(): void
    {
        $cancelledInvoice = $this->createSalesInvoice();
        $cancelledInvoice->cancel();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('cancelled');

        $cancelledInvoice->finalize();
    }

    /**
     * @test
     */
    public function you_can_not_add_a_line_to_a_cancelled_invoice(): void
    {
        $cancelledInvoice = $this->createSalesInvoice();
        $cancelledInvoice->cancel();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('cancelled');

        $cancelledInvoice->addLine(1, '', 1.0, 2.0, null, 'S');
    }

    /**
     * @test
     */
    public function you_can_not_add_a_line_to_a_finalized_invoice(): void
    {
        $finalizedInvoice = $this->createSalesInvoice();
        $finalizedInvoice->finalize();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('finalized');

        $finalizedInvoice->addLine(1, '', 1.0, 2.0, null, 'S');
    }

    /**
     * @return SalesInvoice
     */
    private function createSalesInvoice(): SalesInvoice
    {
        return SalesInvoice::create(
            SalesInvoiceId::fromString(Uuid::uuid4()->toString()),
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

    private function aDate(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    private function aCustomerId(): int
    {
        return 1001;
    }

    private function aQuantityPrecision(): int
    {
        return 3;
    }
}
