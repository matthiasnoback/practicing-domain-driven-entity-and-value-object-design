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
     * @var array<Line>
     */
    private array $lines = [];

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
        /* args */)
    {
    }

    public static function create(
        int $customerId,
        DateTimeImmutable $invoiceDate,
        Currency $currency,
        ?float $exchangeRate,
        int $quantityPrecision
        /* args */): self
    {
        // Nice, because: in future we can have different invoice
        // Nice, because: a way to introduce a domain concept in our code, Meetup::schedule(), Book::draft()
        // Not nice, because: YAGNI

        $instance = new self();
        $instance->customerId = $customerId;
        $instance->invoiceDate = $invoiceDate;
        $instance->currency = $currency;

        if (! $currency->isLedgerCurrency() && $exchangeRate === null) {
            throw new \LogicException('An exchange rate is required when currency is not ledger currency');
        }
        if ($currency->isLedgerCurrency() && $exchangeRate !== null) {
            throw new \LogicException('An exchange rate should not be provided when currency is ledger currency');
        }
        // @TODO introduce ExchangeRate object
        if ($exchangeRate !== null && $exchangeRate <= 0) {
            throw new \InvalidArgumentException('ER should be positive');
        }
        $instance->exchangeRate = $exchangeRate;

        $instance->quantityPrecision = $quantityPrecision;

        return $instance;
    }

    public function addLine(
        Line $newLine
    ): void {
        foreach ($this->lines as $existingLine) {
            if ($existingLine->getProductId() === $newLine->getProductId()) {
                throw new \InvalidArgumentException('You can only add a product once');
            }
        }

        $this->lines[] = $newLine;
    }

    public function totalNetAmount(): float
    {
        $sum = 0.0;

        foreach ($this->lines as $line) {
            // @TODO use a new add() function on Money
            $sum += $line->netAmount()->getAmount();
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
