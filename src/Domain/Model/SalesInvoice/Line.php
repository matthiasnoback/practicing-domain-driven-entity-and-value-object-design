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
     * @var float|null
     */
    private $discount;

    /**
     * @var string
     */
    private $vatCode;

    /**
     * @var float|null
     */
    private $exchangeRate;

    private Currency $currency;

    public function __construct(
        int $productId,
        string $description,
        float $quantity,
        int $quantityPrecision,
        float $tariff,
        ?float $discount,
        string $vatCode,
        ?float $exchangeRate,
        Currency $currency
    ) {
        $this->productId = $productId;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->quantityPrecision = $quantityPrecision;
        $this->tariff = $tariff;
        $this->discount = $discount;
        $this->vatCode = $vatCode;
        $this->exchangeRate = $exchangeRate;
        $this->currency = $currency;
    }

    public function amount(): Money
    {
        return new Money(
            round(round($this->quantity, $this->quantityPrecision) * $this->tariff, 2),
            $this->currency
        );
    }

    public function discountAmount(): float
    {
        // @TODO return Money
        if ($this->discount === null) {
            return 0.0;
        }

        // @TODO add function to Money to calculate discount amount
        // @TODO add DiscountPercentage VO
        return $this->amount()->calculateDiscount($this->discount)->getAmount();
    }

    public function netAmount(): float
    {
        return round($this->amount()->getAmount() - $this->discountAmount(), 2);
    }

    public function vatAmount(): float
    {
        if ($this->vatCode === 'S') {
            $vatRate = 21.0;
        } elseif ($this->vatCode === 'L') {
            if (new DateTime('now') < DateTime::createFromFormat('Y-m-d', '2019-01-01')) {
                $vatRate = 6.0;
            } else {
                $vatRate = 9.0;
            }
        } else {
            throw new InvalidArgumentException('Should not happen');
        }

        return round($this->netAmount() * $vatRate / 100, 2);
    }
}
