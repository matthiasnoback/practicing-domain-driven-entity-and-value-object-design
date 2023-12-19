<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class SalesInvoiceIdTest extends TestCase
{
    public function test_in_and_out(): void
    {
        $this->assertSame(
            '92b88e92-294b-4875-a0e7-c2a7b8ff422c',
            (new SalesInvoiceId('92b88e92-294b-4875-a0e7-c2a7b8ff422c'))->toString()
        );
    }

    public function test_id_should_be_a_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SalesInvoiceId('not a uuid');
    }
}
