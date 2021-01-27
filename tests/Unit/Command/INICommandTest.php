<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit\Command;

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\CertificateType;
use Cube43\Component\Ebics\Command\INICommand;
use Cube43\Component\Ebics\Crypt\GenerateCertificat;
use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\RenderXml;
use Cube43\Component\Ebics\UserCertificate;
use Cube43\Component\Ebics\Version;
use Cube43\Component\Ebics\X509\X509CertificatOptionsGenerator;
use PHPUnit\Framework\TestCase;

class INICommandTest extends TestCase
{
    public function testWith24AndOrderIdNull(): void
    {
        $ebicsServerCaller              = self::createMock(EbicsServerCaller::class);
        $generateCertificat             = self::createMock(GenerateCertificat::class);
        $renderXml                      = self::createMock(RenderXml::class);
        $bank                           = self::createMock(BankInfo::class);
        $keyRing                        = self::createMock(KeyRing::class);
        $x509CertificatOptionsGenerator = self::createMock(X509CertificatOptionsGenerator::class);
        $userCertificate                = self::createMock(UserCertificate::class);
        $document1                      = self::createMock(DOMDocument::class);
        $document2                      = self::createMock(DOMDocument::class);

        $bank->expects(self::exactly(3))->method('getVersion')->willReturn(Version::v24());
        $document1->expects(self::once())->method('toString')->willReturn('titi');
        $document2->expects(self::once())->method('toString')->willReturn('toto');
        $keyRing->expects(self::once())->method('setUserCertificateA')->with($userCertificate);
        $generateCertificat->expects(self::once())->method('__invoke')->with($x509CertificatOptionsGenerator, $keyRing, CertificateType::a())->willReturn($userCertificate);
        $renderXml->expects(self::exactly(2))->method('__invoke')->willReturnOnConsecutiveCalls(
            $document1,
            $document2
        );
        $ebicsServerCaller->expects(self::once())->method('__invoke')->with('toto', $bank);


        $sUT = new INICommand($ebicsServerCaller, $generateCertificat, $renderXml);

        $keyRing = $sUT->__invoke($bank, $keyRing, $x509CertificatOptionsGenerator);

        self::assertSame($keyRing, $keyRing);
    }

    public function testWith24AndOrderNotNull(): void
    {
        $ebicsServerCaller              = self::createMock(EbicsServerCaller::class);
        $generateCertificat             = self::createMock(GenerateCertificat::class);
        $renderXml                      = self::createMock(RenderXml::class);
        $bank                           = self::createMock(BankInfo::class);
        $keyRing                        = self::createMock(KeyRing::class);
        $x509CertificatOptionsGenerator = self::createMock(X509CertificatOptionsGenerator::class);
        $userCertificate                = self::createMock(UserCertificate::class);
        $document1                      = self::createMock(DOMDocument::class);
        $document2                      = self::createMock(DOMDocument::class);

        $bank->expects(self::exactly(3))->method('getVersion')->willReturn(Version::v24());
        $document1->expects(self::once())->method('toString')->willReturn('titi');
        $document2->expects(self::once())->method('toString')->willReturn('toto');
        $keyRing->expects(self::once())->method('setUserCertificateA')->with($userCertificate);
        $generateCertificat->expects(self::once())->method('__invoke')->with($x509CertificatOptionsGenerator, $keyRing, CertificateType::a())->willReturn($userCertificate);
        $renderXml->expects(self::exactly(2))->method('__invoke')->willReturnOnConsecutiveCalls(
            $document1,
            $document2
        );
        $ebicsServerCaller->expects(self::once())->method('__invoke')->with('toto', $bank);


        $sUT = new INICommand($ebicsServerCaller, $generateCertificat, $renderXml);

        $keyRing = $sUT->__invoke($bank, $keyRing, $x509CertificatOptionsGenerator, '10');

        self::assertSame($keyRing, $keyRing);
    }

    /** @return array<int, array<int, Version>> */
    public function versionNot24(): iterable
    {
        yield [Version::v25()];
        yield [Version::v30()];
    }

    /**
     * @dataProvider versionNot24
     */
    public function testFailOnOrderIdNotNullAndVersionNot24(Version $version): void
    {
        $ebicsServerCaller              = self::createMock(EbicsServerCaller::class);
        $generateCertificat             = self::createMock(GenerateCertificat::class);
        $renderXml                      = self::createMock(RenderXml::class);
        $bank                           = self::createMock(BankInfo::class);
        $keyRing                        = self::createMock(KeyRing::class);
        $x509CertificatOptionsGenerator = self::createMock(X509CertificatOptionsGenerator::class);

        $bank->expects(self::exactly(1))->method('getVersion')->willReturn($version);
        $keyRing->expects(self::never())->method('setUserCertificateA');
        $generateCertificat->expects(self::never())->method('__invoke');
        $renderXml->expects(self::never())->method('__invoke');
        $ebicsServerCaller->expects(self::never())->method('__invoke');


        $sUT = new INICommand($ebicsServerCaller, $generateCertificat, $renderXml);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('OrderID only avaiable on ebics 2.4');
        $sUT->__invoke($bank, $keyRing, $x509CertificatOptionsGenerator, '10');
    }
}
