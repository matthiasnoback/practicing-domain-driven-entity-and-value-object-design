<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;
use DateTimeImmutable;

final class SalesInvoice
{
    private SalesInvoiceId $salesInvoiceId;

    /**
     * @var int
     */
    private $customerId;

    private Currency $currency;

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

    private function __construct(
        SalesInvoiceId $salesInvoiceId,
        int $customerId, // @TODO upgrade to VO
        DateTimeImmutable $invoiceDate,
        Currency $currency,
        ?float $exchangeRate,
        int $quantityPrecision,
    )
    {
        // @TODO add validation

        if (! $currency->isLedgerCurrency()) {
            if ($exchangeRate === null) {
                throw new \InvalidArgumentException('Provide an exchange rate');
            }
        }

        // @TODO customer should exist
        $this->customerId = $customerId;

        // @TODO date => book period should be open
        $this->invoiceDate = $invoiceDate;
        $this->currency = $currency;
        // @TODO greater than or equal to 0
        $this->quantityPrecision = $quantityPrecision;
        $this->exchangeRate = $exchangeRate;
        $this->salesInvoiceId = $salesInvoiceId;
    }

    public static function createDraft(
        SalesInvoiceId $salesInvoiceId,
        int $customerId,
        DateTimeImmutable $invoiceDate,
        Currency $currency,
        ?float $exchangeRate,
        int $quantityPrecision,
    ): self
    {
        // @TODO manage invariants

        return new self($salesInvoiceId, $customerId, $invoiceDate, $currency, $exchangeRate, $quantityPrecision);
    }

    public function addLine(
        int $productId,
        string $description,
        float $quantity,
        float $tariff,
        ?float $discount,
        string $vatCode
    ): void {
        if ($this->isCancelled) {
            throw new \LogicException('Invalid state');
        }
        if ($this->isFinalized) {
            throw new \LogicException('Invalid state');
        }
        foreach ($this->lines as $line) {
            if ($line->productId() === $productId) {
                throw new \InvalidArgumentException('Product already added');
            }
        }

        $line = new Line(
            $productId,
            $description,
            $quantity,
            $this->quantityPrecision,
            $tariff,
            $this->currency->toString(),
            $discount,
            $vatCode,
            $this->exchangeRate
        );
        $this->lines[] = $line;
    }

    public function totalNetAmount(): float
    {
        $sum = 0.0;

        foreach ($this->lines as $line) {
            $sum += $line->netAmount()->toFloat();
        }

        return round($sum, 2);
    }

    public function totalNetAmountInLedgerCurrency(): float
    {
        if ($this->currency->isLedgerCurrency() || $this->exchangeRate == null) {
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
        if ($this->currency->isLedgerCurrency() || $this->exchangeRate == null) {
            return $this->totalVatAmount();
        }

        return round($this->totalVatAmount() / $this->exchangeRate, 2);
    }

    public function finalize(): void
    {
        if ($this->isCancelled) {
            throw new \LogicException('Invalid state');
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
            throw new \LogicException('Invalid state');
        }
        $this->isCancelled = true;
    }

    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }

    public function getQuantityPrecision(): int
    {
        return $this->quantityPrecision;
    }

    public function getExchangeRate(): ?float
    {
        return $this->exchangeRate;
    }

    public function getCurrency(): string
    {
        return $this->currency->toString();
    }
}
