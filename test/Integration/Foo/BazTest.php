<?php
declare(strict_types=1);

namespace Foo;

use PHPUnit\Framework\TestCase;

final class BazTest extends TestCase
{
    /**
     * @test
     */
    public function it_works(): void
    {
        self::assertInstanceOf(Baz::class, new Baz());
    }
}
