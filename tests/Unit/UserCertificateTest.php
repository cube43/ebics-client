<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\CertificateType;
use Cube43\Component\Ebics\CertificateX509;
use Cube43\Component\Ebics\Crypt\ExponentAndModulus;
use Cube43\Component\Ebics\PrivateKey;
use Cube43\Component\Ebics\PublicKey;
use Cube43\Component\Ebics\UserCertificate;
use PHPUnit\Framework\TestCase;

use function base64_encode;

/**
 * @coversDefaultClass UserCertificate
 */
class UserCertificateTest extends TestCase
{
    public function testGetter(): void
    {
        $certificatType  = self::createMock(CertificateType::class);
        $privateKey      = self::createMock(PrivateKey::class);
        $publicKey       = self::createMock(PublicKey::class);
        $certificateX509 = self::createMock(CertificateX509::class);

        $privateKey->expects(self::once())->method('value')->willReturn('privateKey');
        $publicKey->expects(self::once())->method('value')->willReturn('publicKey');
        $certificateX509->expects(self::once())->method('value')->willReturn('certX509');
        $certificatType->expects(self::once())->method('value')->willReturn('typea');

        $sUT = new UserCertificate($certificatType, $publicKey, $privateKey, $certificateX509);

        self::assertSame($certificatType, $sUT->getCertificatType());
        self::assertSame($publicKey, $sUT->getPublicKey());
        self::assertSame($privateKey, $sUT->getPrivateKey());
        self::assertSame($certificateX509, $sUT->getCertificatX509());
        self::assertInstanceOf(ExponentAndModulus::class, $sUT->getPublicKey()->getExponentAndModulus());
        self::assertEquals([
            'type' => 'typea',
            'public' => 'cHVibGljS2V5',
            'private' => 'cHJpdmF0ZUtleQ==',
            'content' => 'Y2VydFg1MDk=',
        ], $sUT->jsonSerialize());
    }

    public function testFromArray(): void
    {
        $sUT = UserCertificate::fromArray([
            'type' => 'X',
            'public' => base64_encode('a'),
            'private' => base64_encode('a'),
            'content' => base64_encode('a'),
        ], 'myPassord');

        self::assertSame(CertificateType::x()->value(), $sUT->getCertificatType()->value());
        self::assertSame('a', $sUT->getPublicKey()->value());
        self::assertSame('a', $sUT->getPrivateKey()->value());
        self::assertSame('a', $sUT->getCertificatX509()->value());
    }
}
