<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit\Crypt;

use Cube43\Component\Ebics\BankCertificate;
use Cube43\Component\Ebics\Crypt\BankPublicKeyDigest;
use Cube43\Component\Ebics\Crypt\ExponentAndModulus;
use Cube43\Component\Ebics\PublicKey;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass BankPublicKeyDigest
 */
class BankPublicKeyDigestTest extends TestCase
{
    public function testOk(): void
    {
        $sUT = new BankPublicKeyDigest();

        $bankCertificate    = self::createMock(BankCertificate::class);
        $publicKey          = self::createMock(PublicKey::class);
        $exponentAndModulus = self::createMock(ExponentAndModulus::class);

        $bankCertificate->expects(self::once())->method('getPublicKey')->willReturn($publicKey);
        $publicKey->expects(self::once())->method('getExponentAndModulus')->willReturn($exponentAndModulus);
        $exponentAndModulus->expects(self::once())->method('getExponentToHex')->willReturn('0te');
        $exponentAndModulus->expects(self::once())->method('getModulusToHex')->willReturn('0toto');

        self::assertSame('7MGYDNxJlyOW9C6qX37bK2uJZutrlQAbmo7VvPb5+T0=', $sUT->__invoke($bankCertificate));
    }

    public function testOk2(): void
    {
        $sUT = new BankPublicKeyDigest();

        $bankCertificate    = self::createMock(BankCertificate::class);
        $publicKey          = self::createMock(PublicKey::class);
        $exponentAndModulus = self::createMock(ExponentAndModulus::class);

        $bankCertificate->expects(self::once())->method('getPublicKey')->willReturn($publicKey);
        $publicKey->expects(self::once())->method('getExponentAndModulus')->willReturn($exponentAndModulus);
        $exponentAndModulus->expects(self::once())->method('getExponentToHex')->willReturn('toto');
        $exponentAndModulus->expects(self::once())->method('getModulusToHex')->willReturn('te');

        self::assertSame('7MGYDNxJlyOW9C6qX37bK2uJZutrlQAbmo7VvPb5+T0=', $sUT->__invoke($bankCertificate));
    }
}
