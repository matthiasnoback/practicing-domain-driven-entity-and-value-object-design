<?php

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

class ExchangeRateTest extends TestCase
{
    public function testExchangeRateShouldNotBeZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $rate = new ExchangeRate(0.0, new Currency('USD'), new Currency('EUR'));
    }

    public function testExchangeRateShouldNotBeNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $rate = new ExchangeRate(-1.0, new Currency('USD'), new Currency('EUR'));
    }

    public function testConvert(): void
    {
        $from = new Money(6.0, new Currency('USD'));
        $er = new ExchangeRate(2.0, new Currency('USD'), new Currency('EUR'));
        $this->assertEquals(3.0, $er->convert($from)->asFloat());
    }

    public function testConvertFromDifferentCurrency(): void
    {
        $from = new Money(6.0, new Currency('USD'));
        $er = new ExchangeRate(2.0, new Currency('GBP'), new Currency('EUR'));
        $this->expectException(\InvalidArgumentException::class);
        $er->convert($from);
    }
}
