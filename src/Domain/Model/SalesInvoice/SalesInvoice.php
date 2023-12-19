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

    private function __construct(/* still in use, no required args */)
    {
    }

    public static function create(
        int $customerId,
        DateTimeImmutable $invoiceDate,
        string $currency,
        ?float $exchangeRate,
        int $quantityPrecision,
    ): self {
        $object = new self();

        $object->customerId = $customerId;
        $object->invoiceDate = $invoiceDate;
        $object->currency = new Currency($currency);
        $object->exchangeRate = $exchangeRate;
        $object->quantityPrecision = $quantityPrecision;

        return $object;
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
            $sum += $line->netAmount()->amount();
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

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
// @TODO get rid of toString calls
