<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use DateTime;

final class Line
{
    /**
     * @var int
     */
    private $productId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var int
     */
    private $quantityPrecision;

    /**
     * @var float
     */
    private $tariff;

    /**
     * @var string
     */
    private $currency;

    private Discount $discount;

    /**
     * @var string
     */
    private $vatCode;

    /**
     * @var float|null
     */
    private $exchangeRate;

    private float $vatRate;

    public function __construct(
        int $productId,
        string $description,
        float $quantity,
        int $quantityPrecision,
        float $tariff,
        string $currency,
        Discount $discount,
        string $vatCode,
        float $vatRate,
        ?float $exchangeRate
    ) {
        $this->productId = $productId;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->quantityPrecision = $quantityPrecision;
        $this->tariff = $tariff;
        $this->currency = $currency;
        $this->discount = $discount;
        $this->vatCode = $vatCode;
        $this->vatRate = $vatRate;

        $this->exchangeRate = $exchangeRate;
    }

    public function amount(): float
    {
        return round(round($this->quantity, $this->quantityPrecision) * $this->tariff, 2);
    }

    public function discountAmount(): float
    {
        return $this->discount->discountAmount($this->amount());
    }

    public function netAmount(): float
    {
        return round($this->amount() - $this->discountAmount(), 2);
    }

    public function vatAmount(?DateTime $now = null): float
    {
        return round($this->netAmount() * $this->vatRate / 100, 2);
    }

    public function productId(): int
    {
        return $this->productId;
    }
}
