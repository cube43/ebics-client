<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\E2e\Command;

use Cube43\Component\Ebics\BankCertificate;
use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\CertificateX509;
use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\Command\FDLAknowledgementCommand;
use Cube43\Component\Ebics\Command\FDLCommand;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\FDLParams;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\PrivateKey;
use Cube43\Component\Ebics\Tests\E2e\FakeCrypt;
use Cube43\Component\Ebics\UserCertificate;
use Cube43\Component\Ebics\Version;
use DateTimeImmutable;
use Symfony\Component\HttpClient\MockHttpClient;

class FDLCommandTest extends E2eTestBase
{
    /** @return iterable<string, array<int, Version>> */
    public static function provideVersion(): iterable
    {
        yield 'v24' => [Version::v24()];
        yield 'v25' => [Version::v25()];
        //yield 'v30' => [Version::v30()];
    }

    /** @dataProvider provideVersion */
    public function testOk(Version $version): void
    {
        $tkey  = 'uBrH173GUziiFUQLBQ7MmlCVCoUqOSxj08hEfiSAxkv9RW2uFJes4jXvn1CVD9Kfa0ot8nG7QIb8aWKaix3XdPFbG5gSbZIk2bGowj5FsijwkCDiBFzSsJhpHskIq2crLDk5c4LzVXrEQBJvUIoQ70OdXzJc8/nhThhkG8hJgGMJH35we0JCqzTcQP8DsdjtApX+HN1UnCdPsmhU2vXR2BpvIDgIluJT/dnzWfp5mhfaGKIMA3+Ow+EEuzrwY8JRAP/P9RYyfptjdsNVwUgb9X6xgAkV805JhIf7g9L3GvJjA1/jhYL2Xj97YC+4dWdswe4WTlrJ+3MPA44Dk3zxrwzv+Iu/66PsAboeW8HB7QEXK6AXxEZq0h6Ng2wSfwJSkZE9UU5xUcFG2S/e41M23ZSBMD/mMy5yadPLhQQ3QBP3bwfgee4bnPky1hwN60yUZdaHvF3z92pStV7GCmxcF9Gt420LGciJ2A9yWDpsxtalmLHzozsIeC687WsOzxN/';
        $odata = '8jGZE4A8/CEsmzl4kBNVcbDm+QmBpAMtZhCspu8sSL4GxDBmEEj06Yr+8L30bf6TjtSOJiDeeqnnakVCUvTy2YJMTY8aaSF+OwE/iEclqyRtayCjXxkt/073WwPWlE7P0rRrLzGW/n7BCRJW3ffuMw==';

        $v24 = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<ebicsResponse xmlns="http://www.ebics.org/H003" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="H003" xsi:schemaLocation="http://www.ebics.org/H003 http://www.ebics.org/H003/ebics_response.xsd">
    <header authenticate="true">
        <static>
            <TransactionID>4306ABF98C968ACD32508E0C6D9DC741</TransactionID>
            <NumSegments>1</NumSegments>
        </static>
        <mutable>
            <TransactionPhase>Initialisation</TransactionPhase>
            <SegmentNumber lastSegment="true">1</SegmentNumber>
            <ReturnCode>000000</ReturnCode>
            <ReportText>[EBICS_OK] OK</ReportText>
        </mutable>
    </header>
    
    <AuthSignature><ds:SignedInfo><ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])"><ds:Transforms><ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/></ds:Transforms><ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><ds:DigestValue></ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue></ds:SignatureValue></AuthSignature><body>
        <DataTransfer>
            <DataEncryptionInfo authenticate="true">
                <EncryptionPubKeyDigest Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="E002"></EncryptionPubKeyDigest>
                <TransactionKey>' . $tkey . '</TransactionKey>
            </DataEncryptionInfo>
            <OrderData>' . $odata . '</OrderData>
        </DataTransfer>
        <ReturnCode authenticate="true">000000</ReturnCode>
    </body>
</ebicsResponse>
';

        $v24Ack = '<?xml version="1.0" encoding="UTF-8"?>
<ebicsResponse xmlns="http://www.ebics.org/H003" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H003" xsi:schemaLocation="http://www.ebics.org/H003 http://www.ebics.org/H003/ebics_response.xsd">
   <header authenticate="true">
      <static>
         <TransactionID>32333333393633383436373734363138</TransactionID>
      </static>
      <mutable>
         <TransactionPhase>Receipt</TransactionPhase>
         <ReturnCode>011000</ReturnCode>
         <ReportText>[EBICS_DOWNLOAD_POSTPROCESS_DONE] Positive acknowledgement received</ReportText>
      </mutable>
   </header>
   <AuthSignature>
      <ds:SignedInfo>
         <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
         <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />
         <ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])">
            <ds:Transforms>
               <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />
            <ds:DigestValue>Qp+3gPUkp8qVPzRmKvSztcHl0dBYO1H6oKcpuYgrFho=</ds:DigestValue>
         </ds:Reference>
      </ds:SignedInfo>
      <ds:SignatureValue>Gj2sSVuivNY7157lmMNZW2Oqt68Y4pHIwSBeFkPakAeucOJY6bQPc7fuS3VlBnq3Fe2Vl1ZJNUKx sDDvSzYAD8gpmlgmEu490RV0lLOozWb8VRUCTbYO97pk1LhnBTeUhpbOdQM9geg/CsD7IMhltxgb LpcRVeh33709i4WN0hEBKX8fwxTZRzMYmN1zlk+BQkMVtycaUVnpLRS1KWu4MBGdHDnqzi0/Qn4D h8M3mQLEAC7LcpJvTVPB9GHrdNdNsQRV9AX++iGovEMmEgw+Tb86YRoE/r6pmEcSjgMmNG3HrRle J2fQcZhBy9QLx+iYnWL2kuZIfCQBqtkCx65S6A==</ds:SignatureValue>
   </AuthSignature>
   <body>
      <ReturnCode authenticate="true">000000</ReturnCode>
   </body>
</ebicsResponse>

';

        $v25 = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<ebicsResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="H003" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd">
    <header authenticate="true">
        <static>
            <TransactionID>4306ABF98C968ACD32508E0C6D9DC741</TransactionID>
            <NumSegments>1</NumSegments>
        </static>
        <mutable>
            <TransactionPhase>Initialisation</TransactionPhase>
            <SegmentNumber lastSegment="true">1</SegmentNumber>
            <ReturnCode>000000</ReturnCode>
            <ReportText>[EBICS_OK] OK</ReportText>
        </mutable>
    </header>
    
    <AuthSignature><ds:SignedInfo><ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])"><ds:Transforms><ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/></ds:Transforms><ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><ds:DigestValue></ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue></ds:SignatureValue></AuthSignature><body>
        <DataTransfer>
            <DataEncryptionInfo authenticate="true">
                <EncryptionPubKeyDigest Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="E002"></EncryptionPubKeyDigest>
                <TransactionKey>' . $tkey . '</TransactionKey>
            </DataEncryptionInfo>
            <OrderData>' . $odata . '</OrderData>
        </DataTransfer>
        <ReturnCode authenticate="true">000000</ReturnCode>
    </body>
</ebicsResponse>
';

        $v25Ack = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<ebicsResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="H003" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd">
   <header authenticate="true">
      <static>
         <TransactionID>32333333393633383436373734363138</TransactionID>
      </static>
      <mutable>
         <TransactionPhase>Receipt</TransactionPhase>
         <ReturnCode>011000</ReturnCode>
         <ReportText>[EBICS_DOWNLOAD_POSTPROCESS_DONE] Positive acknowledgement received</ReportText>
      </mutable>
   </header>
   <AuthSignature>
      <ds:SignedInfo>
         <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
         <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />
         <ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])">
            <ds:Transforms>
               <ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />
            <ds:DigestValue>Qp+3gPUkp8qVPzRmKvSztcHl0dBYO1H6oKcpuYgrFho=</ds:DigestValue>
         </ds:Reference>
      </ds:SignedInfo>
      <ds:SignatureValue>Gj2sSVuivNY7157lmMNZW2Oqt68Y4pHIwSBeFkPakAeucOJY6bQPc7fuS3VlBnq3Fe2Vl1ZJNUKx sDDvSzYAD8gpmlgmEu490RV0lLOozWb8VRUCTbYO97pk1LhnBTeUhpbOdQM9geg/CsD7IMhltxgb LpcRVeh33709i4WN0hEBKX8fwxTZRzMYmN1zlk+BQkMVtycaUVnpLRS1KWu4MBGdHDnqzi0/Qn4D h8M3mQLEAC7LcpJvTVPB9GHrdNdNsQRV9AX++iGovEMmEgw+Tb86YRoE/r6pmEcSjgMmNG3HrRle J2fQcZhBy9QLx+iYnWL2kuZIfCQBqtkCx65S6A==</ds:SignatureValue>
   </AuthSignature>
   <body>
      <ReturnCode authenticate="true">000000</ReturnCode>
   </body>
</ebicsResponse>
';

        $versionToXmlResponse = [
            Version::v24()->value() => $v24,
            Version::v25()->value() => $v25,
        ];

        $versionToXmlResponseAck = [
            Version::v24()->value() => $v24Ack,
            Version::v25()->value() => $v25Ack,
        ];

        $sUT = new FDLCommand(
            new EbicsServerCaller(new MockHttpClient($this->getCallback($versionToXmlResponse[$version->value()], $version, true))),
        );

        $bank    = new BankInfo('myHostId', 'http://myurl.com', $version, 'myPartId', 'myUserId');
        $keyRing = new KeyRing('');

        $keyRing = $keyRing->setUserCertificateEAndX(
            new UserCertificate(
                CertificatType::e(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY),
                new CertificateX509(FakeCrypt::RSA_PUBLIC_KEY),
            ),
            new UserCertificate(
                CertificatType::x(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY),
                new CertificateX509(FakeCrypt::RSA_PUBLIC_KEY),
            ),
        );
        $keyRing = $keyRing->setBankCertificate(
            new BankCertificate(
                CertificatType::x(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new CertificateX509(FakeCrypt::RSA_PUBLIC_KEY),
            ),
            new BankCertificate(
                CertificatType::e(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new CertificateX509(FakeCrypt::RSA_PUBLIC_KEY),
            ),
        );

        $response = $sUT->__invoke($bank, $keyRing, new FDLParams('test', 'FR', new DateTimeImmutable(), new DateTimeImmutable()));

        self::assertSame($response->data, '<test><AuthenticationPubKeyInfo><X509Certificate>test</X509Certificate><Modulus>test</Modulus><Exponent>test</Exponent></AuthenticationPubKeyInfo><EncryptionPubKeyInfo><X509Certificate>test</X509Certificate><Modulus>test</Modulus><Exponent>test</Exponent></EncryptionPubKeyInfo></test>');

        $sUTA = new FDLAknowledgementCommand(
            new EbicsServerCaller(new MockHttpClient($this->getCallback($versionToXmlResponseAck[$version->value()], $version, true))),
        );

        $sUTA->__invoke($response);
    }

    /** @dataProvider provideVersion */
    public function testReturnNull(Version $version): void
    {
        $tkey  = 'uBrH173GUziiFUQLBQ7MmlCVCoUqOSxj08hEfiSAxkv9RW2uFJes4jXvn1CVD9Kfa0ot8nG7QIb8aWKaix3XdPFbG5gSbZIk2bGowj5FsijwkCDiBFzSsJhpHskIq2crLDk5c4LzVXrEQBJvUIoQ70OdXzJc8/nhThhkG8hJgGMJH35we0JCqzTcQP8DsdjtApX+HN1UnCdPsmhU2vXR2BpvIDgIluJT/dnzWfp5mhfaGKIMA3+Ow+EEuzrwY8JRAP/P9RYyfptjdsNVwUgb9X6xgAkV805JhIf7g9L3GvJjA1/jhYL2Xj97YC+4dWdswe4WTlrJ+3MPA44Dk3zxrwzv+Iu/66PsAboeW8HB7QEXK6AXxEZq0h6Ng2wSfwJSkZE9UU5xUcFG2S/e41M23ZSBMD/mMy5yadPLhQQ3QBP3bwfgee4bnPky1hwN60yUZdaHvF3z92pStV7GCmxcF9Gt420LGciJ2A9yWDpsxtalmLHzozsIeC687WsOzxN/';
        $odata = '8jGZE4A8/CEsmzl4kBNVcbDm+QmBpAMtZhCspu8sSL4GxDBmEEj06Yr+8L30bf6TjtSOJiDeeqnnakVCUvTy2YJMTY8aaSF+OwE/iEclqyRtayCjXxkt/073WwPWlE7P0rRrLzGW/n7BCRJW3ffuMw==';

        $v24 = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<ebicsResponse xmlns="http://www.ebics.org/H003" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="H003" xsi:schemaLocation="http://www.ebics.org/H003 http://www.ebics.org/H003/ebics_response.xsd">
    <header authenticate="true">
        <static>
            <TransactionID>4306ABF98C968ACD32508E0C6D9DC741</TransactionID>
            <NumSegments>1</NumSegments>
        </static>
        <mutable>
            <TransactionPhase>Initialisation</TransactionPhase>
            <SegmentNumber lastSegment="true">1</SegmentNumber>
            <ReturnCode>000000</ReturnCode>
            <ReportText>[EBICS_OK] No download data available</ReportText>
        </mutable>
    </header>
    
    <AuthSignature><ds:SignedInfo><ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])"><ds:Transforms><ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/></ds:Transforms><ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><ds:DigestValue></ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue></ds:SignatureValue></AuthSignature><body>
        <DataTransfer>
            <DataEncryptionInfo authenticate="true">
                <EncryptionPubKeyDigest Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="E002"></EncryptionPubKeyDigest>
                <TransactionKey>' . $tkey . '</TransactionKey>
            </DataEncryptionInfo>
            <OrderData>' . $odata . '</OrderData>
        </DataTransfer>
        <ReturnCode authenticate="true">090005</ReturnCode>
    </body>
</ebicsResponse>
';

        $v25 = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<ebicsResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="H003" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd">
    <header authenticate="true">
        <static>
            <TransactionID>4306ABF98C968ACD32508E0C6D9DC741</TransactionID>
            <NumSegments>1</NumSegments>
        </static>
        <mutable>
            <TransactionPhase>Initialisation</TransactionPhase>
            <SegmentNumber lastSegment="true">1</SegmentNumber>
            <ReturnCode>000000</ReturnCode>
            <ReportText>[EBICS_OK] No download data available</ReportText>
        </mutable>
    </header>
    
    <AuthSignature><ds:SignedInfo><ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><ds:Reference URI="#xpointer(//*[@authenticate=\'true\'])"><ds:Transforms><ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/></ds:Transforms><ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><ds:DigestValue></ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue></ds:SignatureValue></AuthSignature><body>
        <DataTransfer>
            <DataEncryptionInfo authenticate="true">
                <EncryptionPubKeyDigest Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" Version="E002"></EncryptionPubKeyDigest>
                <TransactionKey>' . $tkey . '</TransactionKey>
            </DataEncryptionInfo>
            <OrderData>' . $odata . '</OrderData>
        </DataTransfer>
        <ReturnCode authenticate="true">090005</ReturnCode>
    </body>
</ebicsResponse>
';

        $versionToXmlResponse = [
            Version::v24()->value() => $v24,
            Version::v25()->value() => $v25,
        ];

        $sUT = new FDLCommand(
            new EbicsServerCaller(new MockHttpClient($this->getCallback($versionToXmlResponse[$version->value()], $version, true))),
        );

        $bank    = new BankInfo('myHostId', 'http://myurl.com', $version, 'myPartId', 'myUserId');
        $keyRing = new KeyRing('');

        $keyRing = $keyRing->setUserCertificateEAndX(
            new UserCertificate(
                CertificatType::e(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY),
                new CertificateX509(FakeCrypt::RSA_PUBLIC_KEY),
            ),
            new UserCertificate(
                CertificatType::x(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new PrivateKey(FakeCrypt::RSA_PRIVATE_KEY),
                new CertificateX509(FakeCrypt::RSA_PUBLIC_KEY),
            ),
        );
        $keyRing = $keyRing->setBankCertificate(
            new BankCertificate(
                CertificatType::x(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new CertificateX509(FakeCrypt::RSA_PUBLIC_KEY),
            ),
            new BankCertificate(
                CertificatType::e(),
                FakeCrypt::RSA_PUBLIC_KEY,
                new CertificateX509(FakeCrypt::RSA_PUBLIC_KEY),
            ),
        );

        self::assertNull($sUT->__invoke($bank, $keyRing, new FDLParams('test', 'FR', new DateTimeImmutable(), new DateTimeImmutable()))->data);
    }
}
