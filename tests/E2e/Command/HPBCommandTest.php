<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\E2e\Command;

use Fezfez\Ebics\BankCertificate;
use Fezfez\Ebics\BankInfo;
use Fezfez\Ebics\CertificateX509;
use Fezfez\Ebics\CertificatType;
use Fezfez\Ebics\Command\HPBCommand;
use Fezfez\Ebics\EbicsServerCaller;
use Fezfez\Ebics\KeyRing;
use Fezfez\Ebics\PrivateKey;
use Fezfez\Ebics\Tests\E2e\FakeCrypt;
use Fezfez\Ebics\UserCertificate;
use Fezfez\Ebics\Version;
use Symfony\Component\HttpClient\MockHttpClient;

class HPBCommandTest extends E2eTestBase
{
    /**
     * @return iterable<int, array<int, Version>>
     */
    public function provideVersion(): iterable
    {
        yield [Version::v24()];
        yield [Version::v25()];
    }

    /** @dataProvider provideVersion */
    public function testOk(Version $version): void
    {
        // encryt TransactionKey with FakeCrypt::RSA_PUBLIC_KEY

        $tkey  = 'uBrH173GUziiFUQLBQ7MmlCVCoUqOSxj08hEfiSAxkv9RW2uFJes4jXvn1CVD9Kfa0ot8nG7QIb8aWKaix3XdPFbG5gSbZIk2bGowj5FsijwkCDiBFzSsJhpHskIq2crLDk5c4LzVXrEQBJvUIoQ70OdXzJc8/nhThhkG8hJgGMJH35we0JCqzTcQP8DsdjtApX+HN1UnCdPsmhU2vXR2BpvIDgIluJT/dnzWfp5mhfaGKIMA3+Ow+EEuzrwY8JRAP/P9RYyfptjdsNVwUgb9X6xgAkV805JhIf7g9L3GvJjA1/jhYL2Xj97YC+4dWdswe4WTlrJ+3MPA44Dk3zxrwzv+Iu/66PsAboeW8HB7QEXK6AXxEZq0h6Ng2wSfwJSkZE9UU5xUcFG2S/e41M23ZSBMD/mMy5yadPLhQQ3QBP3bwfgee4bnPky1hwN60yUZdaHvF3z92pStV7GCmxcF9Gt420LGciJ2A9yWDpsxtalmLHzozsIeC687WsOzxN/';
        $odata = '8jGZE4A8/CEsmzl4kBNVcbDm+QmBpAMtZhCspu8sSL4GxDBmEEj06Yr+8L30bf6TjtSOJiDeeqnnakVCUvTy2YJMTY8aaSF+OwE/iEclqyRtayCjXxkt/073WwPWlE7P0rRrLzGW/n7BCRJW3ffuMw==';

        $versionToXmlResponse = [
            Version::v24()->value() => '<?xml version="1.0" encoding="UTF-8" standalone="no"?><ebicsKeyManagementResponse xmlns="http://www.ebics.org/H003" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H003" xsi:schemaLocation="http://www.ebics.org/H003 http://www.ebics.org/H003/ebics_keymgmt_response.xsd"><header authenticate="true"><static/><mutable><ReturnCode>000000</ReturnCode><ReportText>[EBICS_OK] OK</ReportText></mutable></header><body><DataTransfer><DataEncryptionInfo authenticate="true"><EncryptionPubKeyDigest Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="E002">kWJ3YXAUrfQTbtJRQ5XM1CrN1LbifEAVpo77BYpXEv0=</EncryptionPubKeyDigest><TransactionKey>' . $tkey . '</TransactionKey></DataEncryptionInfo><OrderData>' . $odata . '</OrderData></DataTransfer><ReturnCode authenticate="true">000000</ReturnCode></body></ebicsKeyManagementResponse>',
            Version::v25()->value() => '<?xml version="1.0" encoding="UTF-8"?><ebicsKeyManagementResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H004" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd"><header authenticate="true"><static/><mutable><OrderID>A05Y</OrderID><ReturnCode>000000</ReturnCode><ReportText>[EBICS_OK] OK</ReportText></mutable></header><body><DataTransfer><DataEncryptionInfo authenticate="true"><EncryptionPubKeyDigest Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="E002">kWJ3YXAUrfQTbtJRQ5XM1CrN1LbifEAVpo77BYpXEv0=</EncryptionPubKeyDigest><TransactionKey>' . $tkey . '</TransactionKey></DataEncryptionInfo><OrderData>' . $odata . '</OrderData></DataTransfer><ReturnCode authenticate="true">000000</ReturnCode></body></ebicsKeyManagementResponse>',
        ];

        $sUT = new HPBCommand(
            new EbicsServerCaller(new MockHttpClient($this->getCallback($versionToXmlResponse[$version->value()], $version, true)))
        );

        $bank    = new BankInfo('myHostId', 'http://myurl.com', $version, 'myPartId', 'myUserId');
        $keyRing = new KeyRing('');

        $keyRing = $keyRing->setUserCertificateEAndX(
            new UserCertificate(
                CertificatType::e(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY),
                new CertificateX509(FakeCrypt::X509_PUBLIC)
            ),
            new UserCertificate(
                CertificatType::x(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY),
                new CertificateX509(FakeCrypt::X509_PUBLIC)
            )
        );

        $keyRing = $sUT->__invoke($bank, $keyRing);

        self::assertInstanceOf(BankCertificate::class, $keyRing->getBankCertificateX());
        self::assertInstanceOf(BankCertificate::class, $keyRing->getBankCertificateE());
    }
}
