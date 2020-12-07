<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\Unit;

use Fezfez\Ebics\CertificatType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CertificatTypeTest extends TestCase
{
    public function testVersionConstruct(): void
    {
        self::assertSame('A', CertificatType::a()->value());
        self::assertSame('X', CertificatType::x()->value());
        self::assertSame('E', CertificatType::e()->value());
        self::assertSame('X', CertificatType::fromString('X')->value());
        self::assertTrue(CertificatType::a()->is(CertificatType::a()));
        self::assertTrue(CertificatType::e()->is(CertificatType::e()));
        self::assertTrue(CertificatType::x()->is(CertificatType::x()));
        self::assertFalse(CertificatType::x()->is(CertificatType::e()));
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('unknow certificat type');

        CertificatType::fromString('Z');
    }
}
