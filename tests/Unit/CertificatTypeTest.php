<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\CertificateType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass CertificateType
 */
class CertificatTypeTest extends TestCase
{
    public function testVersionConstruct(): void
    {
        self::assertSame('A', CertificateType::a()->value());
        self::assertSame('A006', CertificateType::a()->toString());
        self::assertSame('X', CertificateType::x()->value());
        self::assertSame('X002', CertificateType::x()->toString());
        self::assertSame('E', CertificateType::e()->value());
        self::assertSame('E002', CertificateType::e()->toString());
        self::assertSame('X', CertificateType::fromString('X')->value());
        self::assertTrue(CertificateType::a()->is(CertificateType::a()));
        self::assertTrue(CertificateType::e()->is(CertificateType::e()));
        self::assertTrue(CertificateType::x()->is(CertificateType::x()));
        self::assertFalse(CertificateType::x()->is(CertificateType::e()));
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('unknow certificat type');

        CertificateType::fromString('Z');
    }
}
