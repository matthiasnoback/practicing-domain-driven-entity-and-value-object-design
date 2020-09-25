<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assert;

final class Amount
{
    private float $amount;
    private string $currency;

    private function __construct(float $amount, string $currency)
    {
        $this->amount = round($amount, 2);
        $this->currency = $currency;
    }

    public static function fromFloat(float $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    public static function zero(string $currency): self
    {
        return new self(0.0, $currency);
    }

    public function add(self $other): self
    {
        Assert::that($this->currency)->eq($other->currency);

        return new self(
            $this->amount + $other->amount,
            $this->currency
        );
    }

    public function asFloat(): float
    {
        return $this->amount;
    }

    public function subtract(self $other): self
    {
        Assert::that($this->currency)->eq($other->currency);

        return new self(
            $this->amount - $other->amount,
            $this->currency
        );
    }

    public function calculateDiscountAmount(float $percentage): self
    {
        return new self(
            $this->amount * ($percentage / 100),
            $this->currency
        );
    }
}
