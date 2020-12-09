<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

final class Discount
{
    private ?float $discount;

    private function __construct(?float $discount)
    {
        $this->discount = $discount;
    }

    public static function fromFloat(?float $discount): self
    {
        return new self($discount);
    }

    public function discountAmount(float $amount): float
    {
        if ($this->discount === null) {
            return 0.0;
        }

        return round($amount * $this->discount / 100, 2);
    }

    public static function noDiscount(): self
    {
        return new self(null);
    }
}
