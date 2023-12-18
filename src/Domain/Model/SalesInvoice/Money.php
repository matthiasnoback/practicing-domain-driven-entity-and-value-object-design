<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class Money
{
    private float $amount;

    private Currency $currency;

    public function __construct(float $amount, Currency $currency)
    {
        $this->amount = round($amount, 2);
        $this->currency = $currency;
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function discount(?float $percentage): Money
    {
        // @TODO extract Percentage
        $percentage = $percentage ?? 0.0;
        return new self($this->amount * ($percentage / 100), $this->currency);
    }

    public function subtract(Money $other): Money
    {
        if (! $other->currency->equals($this->currency)) {
            throw new \InvalidArgumentException('Currencies should be equal');
        }

        return new self($this->amount - $other->amount, $this->currency);
    }
}
