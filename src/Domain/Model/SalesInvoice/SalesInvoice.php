<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assert;
use Assert\Assertion;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;

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

    private function __construct(
        int $customerId,
        DateTimeImmutable $invoiceDate,
        int $quantityPrecision,
        string $currency,
        ?float $exchangeRate
    ) {
        if ($currency !== 'EUR' && $exchangeRate === null) {
            throw new InvalidArgumentException('An exchange rate is required if the currency is not EUR');
        }
        if ($currency === 'EUR' && $exchangeRate !== null) {
            throw new InvalidArgumentException('You cannot use an exchange rate if the currency is EUR');
        }

        $this->customerId = $customerId;
        $this->invoiceDate = $invoiceDate;
        $this->quantityPrecision = $quantityPrecision;
        $this->currency = $currency;
        $this->exchangeRate = $exchangeRate;
    }

    public static function create(
        int $customerId,
        DateTimeImmutable $invoiceDate,
        int $quantityPrecision,
        string $currency,
        ?float $exchangeRate
    ): self {
        return new self(
            $customerId,
            $invoiceDate,
            $quantityPrecision,
            $currency,
            $exchangeRate
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
        Assertion::inArray($vatCode, ['S', 'L']);

        $this->lines[] = new Line(
            $productId,
            $description,
            $quantity,
            $this->quantityPrecision,
            $tariff,
            $this->currency,
            $discount,
            $vatCode,
            $this->exchangeRate
        );
    }

    public function totalNetAmount(): float
    {
        $sum = Amount::zero($this->currency);

        foreach ($this->lines as $line) {
            $sum = $sum->add($line->netAmount());
        }

        return $sum->asFloat();
    }

    public function totalNetAmountInLedgerCurrency(): float
    {
        if ($this->currency === 'EUR') {
            return $this->totalNetAmount();
        }

        Assert::that($this->exchangeRate)->notNull();

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
            throw new LogicException('You cannot finalize this invoice because it has been cancelled');
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
            throw new LogicException('You cannot cancel this invoice because it has been finalized');
        }

        $this->isCancelled = true;
    }

    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }
}
