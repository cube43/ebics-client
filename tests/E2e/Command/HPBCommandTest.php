<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\E2e\Command;

use Cube43\Component\Ebics\BankCertificate;
use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\CertificateX509;
use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\Command\HPBCommand;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\PrivateKey;
use Cube43\Component\Ebics\SymfonyEbicsServerCaller;
use Cube43\Component\Ebics\Tests\E2e\FakeCrypt;
use Cube43\Component\Ebics\UserCertificate;
use Cube43\Component\Ebics\Version;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\MockHttpClient;

class HPBCommandTest extends E2eTestBase
{
    /** @return iterable<string, array<int, Version>> */
    public static function provideVersion(): iterable
    {
        yield 'v24' => [Version::v24()];
        yield 'v25' => [Version::v25()];
        //yield 'v30' => [Version::v30()];
    }

    #[DataProvider('provideVersion')]
    public function testOk(Version $version): void
    {
        // encryt TransactionKey with FakeCrypt::RSA_PUBLIC_KEY

        $tkey  = 'pCRLrJHNXCDLa1AUhBN/tN0hmoZn6eEetISPUlr4Igg0EmkMQ+v7ZKjT2yuL44J5m0z7fuhnHCMSzDKlCzZGTtTBrLoal9KVzZdMWzKcUIHuTXkOyHOuuTe5RgMgBR2QsgR9Tna9p0oUmBsPRWttV0/CAeEb+R6AHuZkuVZgdx0=';
        $odata = 'pF7sOOXT+xNYNTsWgvdeg5baJSoJz/J68YhOCYno2TUPuP6cRxdd34oT4VgGtqLDU6y35+RrOGUUlzP194uVTEUh9YwaXy9mZklbn+seGnySzBvHw8qLy4xntXOS7LscJrFlL3k/NUtd6m7uJLVEPA==';

        $versionToXmlResponse = [
            Version::v24()->value() => '<?xml version="1.0" encoding="UTF-8" standalone="no"?><ebicsKeyManagementResponse xmlns="http://www.ebics.org/H003" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H003" xsi:schemaLocation="http://www.ebics.org/H003 http://www.ebics.org/H003/ebics_keymgmt_response.xsd"><header authenticate="true"><static/><mutable><ReturnCode>000000</ReturnCode><ReportText>[EBICS_OK] OK</ReportText></mutable></header><body><DataTransfer><DataEncryptionInfo authenticate="true"><EncryptionPubKeyDigest Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="E002">kWJ3YXAUrfQTbtJRQ5XM1CrN1LbifEAVpo77BYpXEv0=</EncryptionPubKeyDigest><TransactionKey>' . $tkey . '</TransactionKey></DataEncryptionInfo><OrderData>' . $odata . '</OrderData></DataTransfer><ReturnCode authenticate="true">000000</ReturnCode></body></ebicsKeyManagementResponse>',
            Version::v25()->value() => '<?xml version="1.0" encoding="UTF-8"?><ebicsKeyManagementResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H004" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd"><header authenticate="true"><static/><mutable><OrderID>A05Y</OrderID><ReturnCode>000000</ReturnCode><ReportText>[EBICS_OK] OK</ReportText></mutable></header><body><DataTransfer><DataEncryptionInfo authenticate="true"><EncryptionPubKeyDigest Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="E002">kWJ3YXAUrfQTbtJRQ5XM1CrN1LbifEAVpo77BYpXEv0=</EncryptionPubKeyDigest><TransactionKey>' . $tkey . '</TransactionKey></DataEncryptionInfo><OrderData>' . $odata . '</OrderData></DataTransfer><ReturnCode authenticate="true">000000</ReturnCode></body></ebicsKeyManagementResponse>',
        ];

        $sUT = new HPBCommand(
            new SymfonyEbicsServerCaller(new MockHttpClient($this->getCallback($versionToXmlResponse[$version->value()], $version, true))),
        );

        $bank    = new BankInfo('myHostId', 'http://myurl.com', $version, 'myPartId', 'myUserId');
        $keyRing = new KeyRing('');

        $keyRing = $keyRing->setUserCertificateEAndX(
            new UserCertificate(
                CertificatType::e(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY),
                new CertificateX509(FakeCrypt::X509_PUBLIC),
            ),
            new UserCertificate(
                CertificatType::x(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY),
                new CertificateX509(FakeCrypt::X509_PUBLIC),
            ),
        );

        $keyRing = $sUT->__invoke($bank, $keyRing);

        self::assertInstanceOf(BankCertificate::class, $keyRing->getBankCertificateX());
        self::assertInstanceOf(BankCertificate::class, $keyRing->getBankCertificateE());
    }
}
