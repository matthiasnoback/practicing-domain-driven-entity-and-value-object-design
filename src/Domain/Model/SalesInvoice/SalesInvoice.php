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
    private const STATE_CREATED = 'created';
    private const STATE_CANCELLED = 'cancelled';
    private const STATE_FINALIZED = 'finalized';

    private string $currentState = self::STATE_CREATED;

    private const ALLOWED_TRANSITIONS = [
        self::STATE_CREATED => [
            self::STATE_CANCELLED,
            self::STATE_FINALIZED
        ],
        self::STATE_CANCELLED => [],
        self::STATE_FINALIZED => []
    ];

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
        $this->transitionTo(self::STATE_FINALIZED);
    }

    public function isFinalized(): bool
    {
        return $this->currentState === self::STATE_FINALIZED;
    }

    public function cancel(): void
    {
        $this->transitionTo(self::STATE_CANCELLED);
    }

    public function isCancelled(): bool
    {
        return $this->currentState === self::STATE_CANCELLED;
    }

    private function transitionTo(string $newState): void
    {
        if (!in_array($newState, self::ALLOWED_TRANSITIONS[$this->currentState])) {
            throw new LogicException('You can not transition to the state ' . $newState);
        }

        $this->currentState = $newState;
    }
}
