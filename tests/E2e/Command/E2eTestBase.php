<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\E2e\Command;

use DOMDocument;
use DOMNode;
use Fezfez\Ebics\Crypt\AddRsaSha256PrefixAndReturnAsBinary;
use Fezfez\Ebics\Tests\E2e\FakeCrypt;
use Fezfez\Ebics\Version;
use phpseclib\Crypt\RSA;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\Response\MockResponse;
use XmlValidator\XmlValidator;

use function base64_decode;
use function base64_encode;
use function bin2hex;
use function define;
use function defined;
use function hash;
use function print_r;
use function Safe\sprintf;

class E2eTestBase extends TestCase
{
    /**
     * @return array<callable>
     */
    protected function getCallback(string $response, Version $version, bool $assertSigature): array
    {
        $callback = function ($method, $url, $options) use ($response, $version, $assertSigature) {
            $versionToXsd = [
                Version::v24()->value() => __DIR__ . '/../xsd/24/H003/ebics.xsd',
                Version::v25()->value()  => __DIR__ . '/../xsd/25/ebics_H004.xsd',
                Version::v30()->value()  => __DIR__ . '/../xsd/30/ebics_H005.xsd',
            ];

            $xmlValidator = new XmlValidator();
            $isValid      = $xmlValidator->validate($options['body'], $versionToXsd[$version->value()]);

            self::assertTrue($isValid, print_r($xmlValidator->errors, true));

            if ($assertSigature) {
                $signatureCallback = $this->getSignatureAssertCallback();
                $signatureCallback($options['body']);
            }

            $xmlValidator = new XmlValidator();
            $isValid      = $xmlValidator->validate($response, $versionToXsd[$version->value()]);

            self::assertTrue($isValid, print_r($xmlValidator->errors, true));

            return new MockResponse($response);
        };

        return [$callback, $callback];
    }

    protected function getSignatureAssertCallback(): callable
    {
        return static function (string $response): void {
            $xml = new DOMDocument();
            $xml->loadXML($response);

            $digestOk = static function (string $rawdigest, string $digestValue) {
                return bin2hex(base64_decode($digestValue)) === hash('sha256', $rawdigest);
            };

            $crpyt = static function ($ciphertext) {
                $rsa = new RSA();
                $rsa->setPassword('');
                $rsa->loadKey(FakeCrypt::RSA_PRIVATE_KEY, RSA::PRIVATE_FORMAT_PKCS1);

                if (! defined('CRYPT_RSA_PKCS15_COMPAT')) {
                    define('CRYPT_RSA_PKCS15_COMPAT', true);
                }

                $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);

                return $rsa->encrypt((new AddRsaSha256PrefixAndReturnAsBinary())->__invoke($ciphertext));
            };

            $signatureOk = static function ($signatureRaw, $signatureValue) use ($crpyt) {
                return base64_encode($crpyt(hash('sha256', $signatureRaw, true))) === $signatureValue;
            };

            $findElement = static function (DOMDocument $xml, string $nodeName): DOMNode {
                $node = $xml->getElementsByTagName($nodeName)->item(0);

                if ($node === null) {
                    throw new RuntimeException(sprintf('node "%s" not found', $nodeName));
                }

                return $node;
            };

            self::assertTrue($digestOk($findElement($xml, 'header')->C14N(), $findElement($xml, 'DigestValue')->nodeValue));
            self::assertTrue($signatureOk($findElement($xml, 'SignedInfo')->C14N(), $findElement($xml, 'SignatureValue')->nodeValue));
        };
    }
}
