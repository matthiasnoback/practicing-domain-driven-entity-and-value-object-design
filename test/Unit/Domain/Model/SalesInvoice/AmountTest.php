<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use LogicException;
use PHPUnit\Framework\TestCase;

final class AmountTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_a_float_and_converted_back_to_it(): void
    {
        $amount = 10.0;
        self::assertEquals(
            $amount,
            Amount::fromFloat($amount, 'EUR')->asFloat()
        );
    }

    /**
     * @test
     */
    public function it_rounds_the_provided_float_amount_to_two_decimals(): void
    {
        self::assertEquals(
            Amount::fromFloat(10.12, 'EUR'),
            Amount::fromFloat(10.1234, 'EUR')
        );
        self::assertEquals(
            Amount::fromFloat(10.57, 'EUR'),
            Amount::fromFloat(10.5678, 'EUR')
        );
    }

    /**
     * @test
     */
    public function it_can_not_be_added_to_another_amount_with_a_different_currency(): void
    {
        $this->expectException(LogicException::class);

        Amount::fromFloat(10.0, 'EUR')
            ->add(
                Amount::fromFloat(5.0, 'USD')
            );
    }

    /**
     * @test
     */
    public function you_can_subtract_an_amount_from_another_amount(): void
    {
        self::assertEquals(
            Amount::fromFloat(7.0, 'EUR'),
            Amount::fromFloat(10.0, 'EUR')
                ->subtract(
                    Amount::fromFloat(3.0, 'EUR')
                )
        );
    }

    /**
     * @test
     */
    public function you_can_not_subtract_an_amount_in_a_different_currency(): void
    {
        $this->expectException(LogicException::class);

        Amount::fromFloat(10.0, 'EUR')
            ->subtract(
                Amount::fromFloat(3.0, 'USD')
            );
    }

    /**
     * @test
     */
    public function two_amounts_can_be_added(): void
    {
        self::assertEquals(
            Amount::fromFloat(15.0, 'EUR'),
            Amount::fromFloat(10.0, 'EUR')
                ->add(
                    Amount::fromFloat(5.0, 'EUR')
                )
        );
    }

    /**
     * @test
     */
    public function it_has_a_shortcut_for_creating_an_amount_of_zero(): void
    {
        self::assertEquals(
            Amount::fromFloat(0.0, 'EUR'),
            Amount::zero('EUR')
        );
    }

    /**
     * @test
     */
    public function you_can_apply_a_discount_percentage_to_it(): void
    {
        self::assertEquals(
            Amount::fromFloat(1.0, 'EUR'),
            Amount::fromFloat(10.0, 'EUR')
                ->calculateDiscountAmount(10.0)
        );
    }
}
