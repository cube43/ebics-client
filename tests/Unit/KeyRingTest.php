<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\BankCertificate;
use Cube43\Component\Ebics\CertificateType;
use Cube43\Component\Ebics\CertificateX509;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\PrivateKey;
use Cube43\Component\Ebics\PublicKey;
use Cube43\Component\Ebics\Tests\E2e\FakeCrypt;
use Cube43\Component\Ebics\UserCertificate;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function json_encode;

/**
 * @coversDefaultClass KeyRing
 */
class KeyRingTest extends TestCase
{
    /**
     * @return iterable<int, array<int, string>>
     */
    public function provideGetter(): iterable
    {
        yield ['getUserCertificateA', 'userCertificateA empty'];
        yield ['getUserCertificateX', 'userCertificateX empty'];
        yield ['getUserCertificateE', 'userCertificateE empty'];
        yield ['getBankCertificateX', 'bankCertificateX empty'];
        yield ['getBankCertificateE', 'bankCertificateE empty'];
    }

    /** @dataProvider provideGetter */
    public function testGetterFail(string $getter, string $exceptionMessage): void
    {
        $sUT = new KeyRing('test');

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage($exceptionMessage);

        $sUT->$getter();
    }

    public function testGetterOk(): void
    {
        $userCertificateA = new UserCertificate(
            CertificateType::a(),
            new PublicKey(FakeCrypt::RSA_PUBLIC_KEY),
            new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY, ''),
            self::createMock(CertificateX509::class)
        );
        $userCertificateX = new UserCertificate(
            CertificateType::x(),
            new PublicKey(FakeCrypt::RSA_PUBLIC_KEY),
            new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY, ''),
            self::createMock(CertificateX509::class)
        );
        $userCertificateE = new UserCertificate(
            CertificateType::e(),
            new PublicKey(FakeCrypt::RSA_PUBLIC_KEY),
            new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY, ''),
            self::createMock(CertificateX509::class)
        );
        $bankCertificateX = new BankCertificate(
            CertificateType::x(),
            new PublicKey(FakeCrypt::RSA_PUBLIC_KEY),
            self::createMock(CertificateX509::class)
        );
        $bankCertificateE = new BankCertificate(
            CertificateType::e(),
            new PublicKey(FakeCrypt::RSA_PUBLIC_KEY),
            self::createMock(CertificateX509::class)
        );
        $sUT              = new KeyRing('test', $userCertificateA, $userCertificateX, $userCertificateE, $bankCertificateX, $bankCertificateE);

        self::assertSame($userCertificateA, $sUT->getUserCertificateA());
        self::assertSame($userCertificateX, $sUT->getUserCertificateX());
        self::assertSame($userCertificateE, $sUT->getUserCertificateE());
        self::assertSame($bankCertificateE, $sUT->getBankCertificateE());
        self::assertSame($bankCertificateX, $sUT->getBankCertificateX());
    }

    public function testFailOnMultipleSetUserCertificateA(): void
    {
        $sUT = new KeyRing('test');

        $sUT = $sUT->setUserCertificateA(self::createMock(UserCertificate::class));

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('userCertificateA already exist');

        $sUT->setUserCertificateA(self::createMock(UserCertificate::class));
    }

    public function testFailOnMultipleSetUserCertificateEAndX(): void
    {
        $sUT = new KeyRing('test');

        $sUT = $sUT->setUserCertificateEAndX(self::createMock(UserCertificate::class), self::createMock(UserCertificate::class));

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('userCertificateE and userCertificateX already exist');

        $sUT->setUserCertificateEAndX(self::createMock(UserCertificate::class), self::createMock(UserCertificate::class));
    }

    public function testFailOnMultipleSetBankCertificate(): void
    {
        $sUT = new KeyRing('test');

        $sUT = $sUT->setBankCertificate(self::createMock(BankCertificate::class), self::createMock(BankCertificate::class));

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('bankCertificateX and bankCertificateE already exist');

        $sUT->setBankCertificate(self::createMock(BankCertificate::class), self::createMock(BankCertificate::class));
    }

    public function testFromArray(): void
    {
        $sUT = KeyRing::fromArray([], 'test');

        self::assertSame('{"bankCertificateE":null,"bankCertificateX":null,"userCertificateA":null,"userCertificateE":null,"userCertificateX":null}', json_encode($sUT->jsonSerialize()));
    }

    public function testOk(): void
    {
        $sUT = new KeyRing('test');

        self::assertFalse($sUT->hasBankCertificate());
        self::assertFalse($sUT->hasUserCertificatA());
        self::assertFalse($sUT->hasUserCertificateEAndX());

        self::assertSame('test', $sUT->getRsaPassword());
        self::assertSame('{"bankCertificateE":null,"bankCertificateX":null,"userCertificateA":null,"userCertificateE":null,"userCertificateX":null}', json_encode($sUT));

        $bankCertX = self::createMock(BankCertificate::class);
        $bankCertE = self::createMock(BankCertificate::class);

        $bankCertX->expects(self::exactly(2))->method('jsonSerialize')->willReturn(['hoho' => 'hehe']);
        $bankCertE->expects(self::exactly(2))->method('jsonSerialize')->willReturn(['toto' => 'titi']);

        $newSut = $sUT->setBankCertificate($bankCertX, $bankCertE);

        self::assertFalse($sUT->hasBankCertificate());
        self::assertFalse($sUT->hasUserCertificatA());
        self::assertFalse($sUT->hasUserCertificateEAndX());
        self::assertTrue($newSut->hasBankCertificate());
        self::assertFalse($newSut->hasUserCertificatA());
        self::assertFalse($newSut->hasUserCertificateEAndX());

        self::assertNotSame($newSut, $sUT);
        self::assertSame('{"bankCertificateE":{"toto":"titi"},"bankCertificateX":{"hoho":"hehe"},"userCertificateA":null,"userCertificateE":null,"userCertificateX":null}', json_encode($newSut));

        $userCertA = self::createMock(UserCertificate::class);

        $userCertA->expects(self::once())->method('jsonSerialize')->willReturn(['uhoho' => 'uhehe']);

        $newSutNew = $newSut->setUserCertificateA($userCertA);

        self::assertFalse($sUT->hasBankCertificate());
        self::assertFalse($sUT->hasUserCertificatA());
        self::assertFalse($sUT->hasUserCertificateEAndX());
        self::assertTrue($newSut->hasBankCertificate());
        self::assertFalse($newSut->hasUserCertificatA());
        self::assertFalse($newSut->hasUserCertificateEAndX());
        self::assertTrue($newSutNew->hasBankCertificate());
        self::assertTrue($newSutNew->hasUserCertificatA());
        self::assertFalse($newSutNew->hasUserCertificateEAndX());

        self::assertNotSame($newSutNew, $newSut);
        self::assertSame('{"bankCertificateE":{"toto":"titi"},"bankCertificateX":{"hoho":"hehe"},"userCertificateA":{"uhoho":"uhehe"},"userCertificateE":null,"userCertificateX":null}', json_encode($newSutNew));
    }
}
