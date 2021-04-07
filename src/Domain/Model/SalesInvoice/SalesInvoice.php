<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;
use DateTimeImmutable;
use LogicException;
use Ramsey\Uuid\Uuid;

final class SalesInvoice
{
    /**
     * @var int
     */
    private $customerId;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var float|null
     */
    private $exchangeRate;

    /**
     * @var int
     */
    private $quantityPrecision;

    /**
     * @var Line[]
     */
    private $lines = [];

    /**
     * @var bool
     */
    private $isFinalized = false;

    /**
     * @var bool
     */
    private $isCancelled = false;

    /**
     * @var DateTimeImmutable
     */
    private $invoiceDate;

    private SalesInvoiceId $salesInvoiceId;

    private function __construct(
        SalesInvoiceId $salesInvoiceId,
        int $customerId,
        DateTimeImmutable $invoiceDate,
        string $currency,
        ?float $exchangeRate,
        int $quantityPrecision
    ) {
        if ($currency !== 'EUR' && $exchangeRate === null) {
            throw new LogicException(
                'When using a currency other than EUR you should provide an exchange rate.'
            );
        }
        $this->customerId = $customerId;
        $this->invoiceDate = $invoiceDate;
        $this->currency = $currency;
        $this->exchangeRate = $exchangeRate;
        $this->quantityPrecision = $quantityPrecision;
        $this->salesInvoiceId = $salesInvoiceId;
    }

    public static function create(
        SalesInvoiceId $salesInvoiceId,
        int $customerId,
        DateTimeImmutable $invoiceDate,
        string $currency,
        ?float $exchangeRate,
        int $quantityPrecision
    ): self {
        return new self(
            $salesInvoiceId,
            $customerId,
            $invoiceDate,
            $currency,
            $exchangeRate,
            $quantityPrecision
        );
    }

    public function addLine(
        int $productId,
        string $description,
        float $quantity,
        float $tariff,
        ?float $discount,
        string $vatCode
    ): void {
        if ($this->isFinalized || $this->isCancelled) {
            throw new LogicException('You can not add lines to finalized or cancelled invoices');
        }

        Assertion::inArray($vatCode, ['S', 'L']);

        foreach ($this->lines as $line) {
            if ($line->productId() === $productId) {
                throw new LogicException('The invoice already has a line with this product');
            }
        }

        $this->lines[] = new Line(
            $productId,
            $description,
            $quantity,
            $this->quantityPrecision,
            $tariff,
            $this->currency,
            $discount === null
                ? Discount::noDiscount()
                : new Discount($discount),
            $vatRate = VatRate::forCodeAndDate(
                $vatCode,
                $this->invoiceDate
            ),
            $this->exchangeRate
        );
    }

    public function totalNetAmount(): float
    {
        $sum = 0.0;

        foreach ($this->lines as $line) {
            $sum += $line->netAmount();
        }

        return round($sum, 2);
    }

    public function totalNetAmountInLedgerCurrency(): float
    {
        if ($this->currency === 'EUR' || $this->exchangeRate == null) {
            return $this->totalNetAmount();
        }

        return round($this->totalNetAmount() / $this->exchangeRate, 2);
    }

    public function totalVatAmount(): float
    {
        $sum = 0.0;

        foreach ($this->lines as $line) {
            $sum += $line->vatAmount();
        }

        return round($sum, 2);
    }

    public function totalVatAmountInLedgerCurrency(): float
    {
        if ($this->currency === 'EUR' || $this->exchangeRate == null) {
            return $this->totalVatAmount();
        }

        return round($this->totalVatAmount() / $this->exchangeRate, 2);
    }

    public function finalize(): void
    {
        if ($this->isCancelled) {
            throw new LogicException('You cannot finalize a cancelled invoice');
        }

        $this->isFinalized = true;
    }

    public function isFinalized(): bool
    {
        return $this->isFinalized;
    }

    public function cancel(): void
    {
        if ($this->isFinalized) {
            throw new LogicException('You cannot cancel a finalized invoice');
        }

        $this->isCancelled = true;
    }

    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }
}
