<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use Assert\Assertion;
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

    public function __construct(
        int $productId,
        string $description,
        float $quantity,
        int $quantityPrecision,
        float $tariff,
        string $currency,
        ?float $discount,
        string $vatCode,
        ?float $exchangeRate
    ) {
        Assertion::inArray($vatCode, ['S', 'L']);

        Assertion::greaterThan($quantity, 0.0, 'The quantity should be larger than 0.0');

        // @TODO discount should be positive or 0.0 or null, and should be a VO

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

    public function amount(): Money
    {
        // @TODO remove the rounding
        return new Money(
            round($this->quantity, $this->quantityPrecision) * $this->tariff,
            Currency::createWith($this->currency)
        );
    }

    public function discountAmount(): Money
    {
        if ($this->discount === null) {
            return Money::fromFloat(0.0, Currency::createWith($this->currency));
        }

        return $this->amount()->takePercentage($this->discount);
    }

    public function netAmount(): Money
    {
        return $this->amount()->subtract($this->discountAmount());
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

        return round($this->netAmount()->toFloat() * $vatRate / 100, 2);
    }

    public function productId(): int
    {
        return $this->productId;
    }
}
