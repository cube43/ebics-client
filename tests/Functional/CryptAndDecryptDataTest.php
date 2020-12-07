<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\Functional;

use Fezfez\Ebics\CertificatType;
use Fezfez\Ebics\Crypt\AddRsaSha256PrefixAndReturnAsBinary;
use Fezfez\Ebics\Crypt\DecryptOrderDataContent;
use Fezfez\Ebics\Crypt\EncrytSignatureValueWithUserPrivateKey;
use Fezfez\Ebics\Crypt\GenerateCertificat;
use Fezfez\Ebics\DOMDocument;
use Fezfez\Ebics\KeyRing;
use Fezfez\Ebics\OrderDataEncrypted;
use Fezfez\Ebics\PrivateKey;
use Fezfez\Ebics\X509\DefaultX509OptionGenerator;
use phpseclib\Crypt\AES;
use PHPUnit\Framework\TestCase;

use function Safe\gzcompress;

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

        $orderData = $this->aesCrypt((new AddRsaSha256PrefixAndReturnAsBinary())->__invoke($xmlData), gzcompress($xmlData));

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
}
