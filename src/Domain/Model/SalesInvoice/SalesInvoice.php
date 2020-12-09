<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;
use DateTime;
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

    public function __construct(
        int $customerId,
        DateTimeImmutable $invoiceDate,
        string $currency,
        ?float $exchangeRate,
        int $quantityPrecision
    ) {
        if ($currency !== 'EUR' && $exchangeRate === null) {
            throw new InvalidArgumentException(
                sprintf(
                    'You have to provide an exchange rate when creating an invoice with currency "%s"',
                    $currency
                )
            );
        }

        $this->customerId = $customerId;
        $this->invoiceDate = $invoiceDate;
        $this->currency = $currency;
        $this->exchangeRate = $exchangeRate;
        $this->quantityPrecision = $quantityPrecision;
    }

    public function addLine(
        int $productId,
        string $description,
        float $quantity,
        float $tariff,
        Discount $discount,
        string $vatCode,
        float $vatRate
    ): void {
        Assertion::inArray($vatCode, ['S', 'L']);
        Assertion::greaterThan($quantity, 0.0, 'The line quantity should be greater than 0.0');

        foreach ($this->lines as $line) {
            if ($line->productId() === $productId) {
                throw new LogicException(
                    sprintf(
                        'You can not add another line for product "%s"',
                        $productId
                    )
                );
            }
        }

        $this->lines[] = new Line(
            $productId,
            $description,
            $quantity,
            $this->quantityPrecision,
            $tariff,
            $this->currency,
            $discount,
            $vatCode,
            $vatRate,
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
        if ($this->currency === 'EUR') {
            return $this->totalNetAmount();
        }

        Assertion::float($this->exchangeRate);

        return round($this->totalNetAmount() / $this->exchangeRate, 2);
    }

    public function totalVatAmount(?DateTime $now = null): float
    {
        $sum = 0.0;

        foreach ($this->lines as $line) {
            $sum += $line->vatAmount($now);
        }

        return round($sum, 2);
    }

    public function totalVatAmountInLedgerCurrency(?DateTime $now = null): float
    {
        if ($this->currency === 'EUR') {
            return $this->totalVatAmount($now);
        }

        Assertion::float($this->exchangeRate);

        return round($this->totalVatAmount($now) / $this->exchangeRate, 2);
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
}
