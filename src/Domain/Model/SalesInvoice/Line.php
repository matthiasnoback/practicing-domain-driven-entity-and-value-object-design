<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use DateTimeImmutable;

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

    /**
     * @var Discount
     */
    private $discount;

    /**
     * @var VatRate
     */
    private $vatRate;

    /**
     * @var float|null
     */
    private $exchangeRate;

    public function __construct(
        int $productId,
        string $description,
        float $quantity,
        int $quantityPrecision,
        float $tariff,
        string $currency,
        ?float $discount,
        VatRate $vatRate,
        ?float $exchangeRate
    ) {
        $this->productId = $productId;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->quantityPrecision = $quantityPrecision;
        $this->tariff = $tariff;
        $this->currency = $currency;
        $this->discount = $discount === null
            ? Discount::noDiscount()
            : new Discount($discount);
        $this->vatRate = $vatRate;
        $this->exchangeRate = $exchangeRate;
    }

    public function amount(): float
    {
        return round(round($this->quantity, $this->quantityPrecision) * $this->tariff, 2);
    }

    public function discountAmount(): float
    {
        return $this->discount->calculateFor($this->amount());
    }

    public function netAmount(): float
    {
        return round($this->amount() - $this->discountAmount(), 2);
    }

    public function vatAmount(): float
    {
        return $this->vatRate->calculateVatForAmount($this->netAmount());
    }
}
