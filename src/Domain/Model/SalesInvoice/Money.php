<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class Money
{
    private float $amount;

    public function __construct(float $amount)
    {
        // @TODO enforce Currency to be provided
        $this->amount = round($amount, 2);
    }

    public static function fromFloat(float $amount): self
    {
        return new self($amount);
    }

    public function toFloat(): float
    {
        return $this->amount;
    }

    public function takePercentage(float $percentage): self
    {
        return new self(round($this->amount * ($percentage / 100), 2));
    }

    public function subtract(Money $other): self
    {
        return new self($this->amount - $other->amount);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount;
    }
}
