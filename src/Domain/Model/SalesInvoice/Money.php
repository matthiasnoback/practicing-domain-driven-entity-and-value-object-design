<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class Money
{
    private float $amount;

    private Currency $currency;

    public function __construct(float $amount, Currency $currency)
    {
        // @TODO enforce Currency to be provided
        $this->amount = round($amount, 2);
        $this->currency = $currency;
    }

    public static function fromFloat(float $amount, Currency $currency): self
    {
        return new self($amount, $currency);
    }

    public function toFloat(): float
    {
        return $this->amount;
    }

    public function takePercentage(float $percentage): self
    {
        return new self(
            round($this->amount * ($percentage / 100), 2),
            $this->currency
        );
    }

    public function subtract(Money $other): self
    {
        // @TODO enforce currency of $other to be the same as $this->>currency
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount;
    }
}
