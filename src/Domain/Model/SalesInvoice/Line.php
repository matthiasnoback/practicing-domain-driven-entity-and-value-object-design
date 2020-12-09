<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use DateTime;
use InvalidArgumentException;

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

    public function __construct(
        int $productId,
        string $description,
        float $quantity,
        int $quantityPrecision,
        float $tariff,
        string $currency,
        Discount $discount,
        string $vatCode,
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
        $vatRate = $this->vatRateForVatCodeAtDate($now ?? new DateTime());

        return round($this->netAmount() * $vatRate / 100, 2);
    }

    private function vatRateForVatCodeAtDate(DateTime $now): float
    {
        if ($this->vatCode === 'S') {
            return 21.0;
        } elseif ($this->vatCode === 'L') {
            if ($now < DateTime::createFromFormat('Y-m-d', '2019-01-01')) {
                return 6.0;
            } else {
                return 9.0;
            }
        }

        throw new InvalidArgumentException('Could not determine the VAT rate');
    }
}
