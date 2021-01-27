<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Functional;

use Cube43\Component\Ebics\CertificateType;
use Cube43\Component\Ebics\Crypt\AddRsaSha256PrefixAndReturnAsBinary;
use Cube43\Component\Ebics\Crypt\DecryptOrderDataContent;
use Cube43\Component\Ebics\Crypt\EncrytSignatureValueWithUserPrivateKey;
use Cube43\Component\Ebics\Crypt\GenerateCertificat;
use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\OrderDataEncrypted;
use Cube43\Component\Ebics\UserCertificate;
use Cube43\Component\Ebics\X509\DefaultX509OptionGenerator;
use phpseclib\Crypt\AES;
use PHPUnit\Framework\TestCase;

use function Safe\gzcompress;

use const OPENSSL_ZERO_PADDING;

class CryptAndDecryptDataTest extends TestCase
{
    public function testCryptWithPublicAndDecryptWithPrivate(): void
    {
        $generateCert            = new GenerateCertificat();
        $encrypted               = new EncrytSignatureValueWithUserPrivateKey();
        $decryptOrderDataContent = new DecryptOrderDataContent();
        $keyRing                 = new KeyRing('helllooo!');

        $certificatE = $generateCert->__invoke(new DefaultX509OptionGenerator(), $keyRing, CertificateType::e());
        $keyRing     = $keyRing->setUserCertificateEAndX($certificatE, self::createMock(UserCertificate::class));

        $transactionKey = $encrypted->__invoke($keyRing, $keyRing->getUserCertificateE()->getPublicKey(), '<hello></hello>');
        $orderData      = $this->encryptOrderData('<hello></hello>');

        $orderDataEncrypted = new OrderDataEncrypted($orderData, $transactionKey);
        $xmlDocument        = new DOMDocument($decryptOrderDataContent->__invoke($keyRing->getUserCertificateE()->getPrivateKey(), $orderDataEncrypted));

        self::assertXmlStringEqualsXmlString('<hello></hello>', $xmlDocument->toString());
    }

    public function testCryptWithPrivateAndDecryptWithPublic(): void
    {
        $generateCert            = new GenerateCertificat();
        $encrypted               = new EncrytSignatureValueWithUserPrivateKey();
        $decryptOrderDataContent = new DecryptOrderDataContent();
        $keyRing                 = new KeyRing('helllooo!');

        $certificatE = $generateCert->__invoke(new DefaultX509OptionGenerator(), $keyRing, CertificateType::e());
        $keyRing     = $keyRing->setUserCertificateEAndX($certificatE, self::createMock(UserCertificate::class));

        $transactionKey = $encrypted->__invoke($keyRing, $keyRing->getUserCertificateE()->getPrivateKey(), '<hello></hello>');
        $orderData      = $this->encryptOrderData('<hello></hello>');

        $orderDataEncrypted = new OrderDataEncrypted($orderData, $transactionKey);
        $xmlDocument        = new DOMDocument($decryptOrderDataContent->__invoke($keyRing->getUserCertificateE()->getPublicKey(), $orderDataEncrypted));

        self::assertXmlStringEqualsXmlString('<hello></hello>', $xmlDocument->toString());
    }

    private function encryptOrderData(string $key): string
    {
        $aes = new AES(AES::MODE_CBC);
        $aes->setKeyLength(128);
        $aes->setKey((new AddRsaSha256PrefixAndReturnAsBinary())->__invoke($key));

        // phpcs:ignore
        $aes->openssl_options = OPENSSL_ZERO_PADDING;

        return $aes->encrypt(gzcompress($key));
    }
}
