<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\CertificateX509;
use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\Crypt\ExponentAndModulus;
use Cube43\Component\Ebics\Models\Certificate;
use Cube43\Component\Ebics\PrivateKey;
use Cube43\Component\Ebics\UserCertificate;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass Certificate */
class UserCertificateTest extends TestCase
{
    public function testGetter(): void
    {
        $certificatType  = self::createMock(CertificatType::class);
        $privateKey      = self::createMock(PrivateKey::class);
        $certificateX509 = self::createMock(CertificateX509::class);

        $privateKey->expects(self::once())->method('value')->willReturn('privateKey');
        $certificateX509->expects(self::once())->method('value')->willReturn('certX509');
        $certificatType->expects(self::once())->method('value')->willReturn('typea');

        $sUT = new UserCertificate($certificatType, 'test2', $privateKey, $certificateX509);

        self::assertSame($certificatType, $sUT->getCertificatType());
        self::assertSame('test2', $sUT->getPublicKey());
        self::assertSame($privateKey, $sUT->getPrivateKey());
        self::assertSame($certificateX509, $sUT->getCertificatX509());
        self::assertInstanceOf(ExponentAndModulus::class, $sUT->getPublicKeyDetails());
        self::assertEquals([
            'type' => 'typea',
            'public' => 'dGVzdDI=',
            'private' => 'cHJpdmF0ZUtleQ==',
            'content' => 'Y2VydFg1MDk=',
        ], $sUT->jsonSerialize());
    }
}
