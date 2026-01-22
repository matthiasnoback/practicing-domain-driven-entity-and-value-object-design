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
    private $quantityPrecision = 3;

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

    private function __construct()
    {

    }

    public static function createDraft(
        int $customerId,
        DateTimeImmutable $invoiceDate,
        string $currency = 'EUR',
        ?float $exchangeRate = null,
    ): self
    {
        $invoice = new self();
        $invoice->setCustomerId($customerId);
        $invoice->setInvoiceDate($invoiceDate);
        $invoice->setCurrency($currency);
        if ($currency !== 'EUR' && $exchangeRate === null) {
            throw new \InvalidArgumentException('Exchange rate must be set');
        }
        $invoice->setExchangeRate($exchangeRate);

        return $invoice;
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
        int $productId,
        string $description,
        float $quantity,
        float $tariff,
        ?float $discount,
        string $vatCode
    ): void {
        $this->lines[] = new Line($productId,
            $description,
            $quantity,
            $this->quantityPrecision,
            $tariff,
            (string)$this->currency,
            $discount,
            $vatCode,
            $this->exchangeRate?->rateAsFloat());
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

    public function getCustomerId(): int
    {
        return $this->customerId;
    }
}
