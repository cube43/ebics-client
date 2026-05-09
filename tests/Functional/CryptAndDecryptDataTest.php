<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Functional;

use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\Crypt\DecryptOrderDataContent;
use Cube43\Component\Ebics\Crypt\GenerateCertificat;
use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\OrderDataEncrypted;
use Cube43\Component\Ebics\X509\DefaultX509OptionGenerator;
use ErrorException;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PublicKey as RsaPublicKey;
use PHPUnit\Framework\TestCase;

use function assert;
use function base64_encode;
use function gzcompress;
use function random_bytes;
use function str_pad;
use function str_repeat;
use function strlen;

class CryptAndDecryptDataTest extends TestCase
{
    public function testFail(): void
    {
        $generateCert            = new GenerateCertificat();
        $decryptOrderDataContent = new DecryptOrderDataContent();
        $password                = new KeyRing('myPass');

        $xmlData = '<test><AuthenticationPubKeyInfo><X509Certificate>test</X509Certificate><Modulus>test</Modulus><Exponent>test</Exponent></AuthenticationPubKeyInfo><EncryptionPubKeyInfo><X509Certificate>test</X509Certificate><Modulus>test</Modulus><Exponent>test</Exponent></EncryptionPubKeyInfo></test>';

        $certE  = $generateCert->__invoke(new DefaultX509OptionGenerator(), $password, CertificatType::e());
        $aesKey = random_bytes(16);

        /** @var RsaPublicKey $pubKey */
        $pubKey = RSA::load($certE->getPublicKey());
        $transactionKey = $pubKey->withPadding(RSA::ENCRYPTION_PKCS1)->encrypt($aesKey);

        $orderData = base64_encode($this->aesCrypt($aesKey, self::gzcompress($xmlData)));

        $keyRing = new KeyRing('myPass');
        $keyRing = $keyRing->setUserCertificateEAndX($certE, $certE);

        self::assertXmlStringEqualsXmlString($xmlData, (new DOMDocument($decryptOrderDataContent->__invoke($keyRing, new OrderDataEncrypted($orderData, $transactionKey))))->toString());
    }

    private function aesCrypt(string $key, string $cypher): string
    {
        $aes = new AES('cbc');
        $aes->setKeyLength(128);
        $aes->setKey($key);
        $aes->setIV(str_repeat("\0", 16));
        $aes->disablePadding();

        $blockSize = 16;
        $remainder = strlen($cypher) % $blockSize;
        if ($remainder !== 0) {
            $cypher = str_pad($cypher, strlen($cypher) + $blockSize - $remainder, "\0");
        }

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
