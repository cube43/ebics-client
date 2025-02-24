<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Functional;

use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\Crypt\AddRsaSha256PrefixAndReturnAsBinary;
use Cube43\Component\Ebics\Crypt\DecryptOrderDataContent;
use Cube43\Component\Ebics\Crypt\EncrytSignatureValueWithUserPrivateKey;
use Cube43\Component\Ebics\Crypt\GenerateCertificat;
use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\OrderDataEncrypted;
use Cube43\Component\Ebics\PrivateKey;
use Cube43\Component\Ebics\X509\DefaultX509OptionGenerator;
use ErrorException;
use phpseclib\Crypt\AES;
use PHPUnit\Framework\TestCase;

use function gzcompress;

use const OPENSSL_ZERO_PADDING;

class CryptAndDecryptDataTest extends TestCase
{
    public function testFail(): void
    {
        $generateCert            = new GenerateCertificat();
        $encrypted               = new EncrytSignatureValueWithUserPrivateKey();
        $decryptOrderDataContent = new DecryptOrderDataContent();
        $password                = new KeyRing('myPass');

        $xmlData = '<test><AuthenticationPubKeyInfo><X509Certificate>test</X509Certificate><Modulus>test</Modulus><Exponent>test</Exponent></AuthenticationPubKeyInfo><EncryptionPubKeyInfo><X509Certificate>test</X509Certificate><Modulus>test</Modulus><Exponent>test</Exponent></EncryptionPubKeyInfo></test>';

        $certE          = $generateCert->__invoke(new DefaultX509OptionGenerator(), $password, CertificatType::e());
        $transactionKey = $encrypted->__invoke($password, new PrivateKey($certE->getPublicKey()), $xmlData);

        $orderData = $this->aesCrypt((new AddRsaSha256PrefixAndReturnAsBinary())->__invoke($xmlData), self::gzcompress($xmlData));

        $keyRing = new KeyRing('myPass');
        $keyRing = $keyRing->setUserCertificateEAndX($certE, $certE);

        self::assertXmlStringEqualsXmlString($xmlData, (new DOMDocument($decryptOrderDataContent->__invoke($keyRing, new OrderDataEncrypted($orderData, $transactionKey))))->toString());
    }

    private function aesCrypt(string $key, string $cypher): string
    {
        $aes = new AES(AES::MODE_CBC);
        $aes->setKeyLength(128);
        $aes->setKey($key);

        // phpcs:ignore
        $aes->openssl_options = OPENSSL_ZERO_PADDING;

        return $aes->encrypt($cypher);
    }

    private static function gzcompress(string $string): string
    {
        $safeResult = gzcompress($string);
        if ($safeResult === false) {
            throw new ErrorException('An error occured');
        }

        return $safeResult;
    }
}
