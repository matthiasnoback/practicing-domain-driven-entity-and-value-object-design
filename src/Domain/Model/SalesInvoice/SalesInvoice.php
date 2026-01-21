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

    private ?ExchangeRate $exchangeRate = null;

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

    public function __construct()
    {
    }

    public function setCustomerId(int $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function setInvoiceDate(DateTimeImmutable $invoiceDate): void
    {
        $this->invoiceDate = $invoiceDate;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = new Currency($currency);
    }

    public function setExchangeRate(?float $exchangeRate): void
    {
        if ($exchangeRate === null) {
            $this->exchangeRate = null;
            return;
        }

        $this->exchangeRate = new ExchangeRate($exchangeRate, $this->currency, new Currency('EUR'));
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

    public function totalNetAmount(): Money
    {
        $sum = 0.0;

        foreach ($this->lines as $line) {
            $sum += $line->netAmount();
        }

        return new Money($sum, $this->currency);
    }

    public function totalNetAmountInLedgerCurrency(): Money
    {
        if ((string)$this->currency === 'EUR' || $this->exchangeRate === null) {
            return $this->totalNetAmount();
        }

        return $this->exchangeRate->convert($this->totalNetAmount());
    }

    public function totalVatAmount(): Money
    {
        $sum = 0.0;

        foreach ($this->lines as $line) {
            $sum += $line->vatAmount();
        }

        return new Money($sum, $this->currency);
    }

    public function totalVatAmountInLedgerCurrency(): float
    {
        if ((string)$this->currency === 'EUR' || $this->exchangeRate === null) {
            return $this->totalVatAmount()->asFloat();
        }

        return $this->exchangeRate->convert($this->totalVatAmount())->asFloat();
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
        if ($this->exchangeRate === null) {
            return null;
        }

        return $this->exchangeRate->rateAsFloat();
    }

    public function getCurrency(): string
    {
        return (string) $this->currency;
    }
}
