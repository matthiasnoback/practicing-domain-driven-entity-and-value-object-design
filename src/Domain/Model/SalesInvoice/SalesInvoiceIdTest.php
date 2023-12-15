<?php
declare(strict_types=1);

namespace Domain\Model\SalesInvoice;

use PHPUnit\Framework\TestCase;

final class SalesInvoiceIdTest extends TestCase
{
    public function test_create_from_uuid(): void
    {
        $uuid = '003c527e-4aab-4879-b04a-590047910dc9';
        $this->assertSame($uuid, SalesInvoiceId::fromUuid($uuid)->toString());
    }

    public function test_create_from_invalid_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        SalesInvoiceId::fromUuid('invalid');
    }
}
