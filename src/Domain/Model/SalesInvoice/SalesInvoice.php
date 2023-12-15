<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;
use DateTimeImmutable;

final class SalesInvoice
{
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
        $this->setCustomerId($customerId);

        // @TODO date => book period should be open
        $this->setInvoiceDate($invoiceDate);
        $this->setCurrency($currency);
        // @TODO greater than or equal to 0
        $this->setQuantityPrecision($quantityPrecision);
        $this->setExchangeRate($exchangeRate);
    }

    public static function createDraft(
        int $customerId,
        DateTimeImmutable $invoiceDate,
        Currency $currency,
        ?float $exchangeRate,
        int $quantityPrecision,
    ): self
    {
        // @TODO manage invariants

        return new self($customerId, $invoiceDate, $currency, $exchangeRate, $quantityPrecision);
    }

    public function setCustomerId(int $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function setInvoiceDate(DateTimeImmutable $invoiceDate): void
    {
        $this->invoiceDate = $invoiceDate;
    }

    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }

    public function setExchangeRate(?float $exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }

    public function setQuantityPrecision(int $quantityPrecision): void
    {
        $this->quantityPrecision = $quantityPrecision;
    }

    public function addLine(
        Line $line
    ): void {
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

    public function setFinalized(bool $finalized): void
    {
        $this->isFinalized = $finalized;
    }

    public function isFinalized(): bool
    {
        return $this->isFinalized;
    }

    public function setCancelled(bool $cancelled): void
    {
        $this->isCancelled = $cancelled;
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
