<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\E2e\Command;

use Cube43\Component\Ebics\Crypt\AddRsaSha256PrefixAndReturnAsBinary;
use Cube43\Component\Ebics\Tests\E2e\FakeCrypt;
use Cube43\Component\Ebics\Version;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXpath;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\Response\MockResponse;
use XmlValidator\XmlValidator;

use function assert;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function hash;
use function openssl_pkey_get_private;
use function openssl_private_encrypt;
use function print_r;
use function sprintf;
use function trim;

use const OPENSSL_PKCS1_PADDING;

class E2eTestBase extends TestCase
{
    /** @return array<callable> */
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

            $digestOk   = static function (string $rawdigest, string $digestValue) {
                return bin2hex(base64_decode($digestValue)) === hash('sha256', $rawdigest);
            };
            $digestDump = static function (string $rawdigest, string $digestValue) {
                return bin2hex(base64_decode($digestValue)) . ' not equals ' . hash('sha256', $rawdigest);
            };

            $crpyt = static function ($ciphertext) {
                $privateKeyRes = openssl_pkey_get_private(FakeCrypt::RSA_PRIVATE_KEY);
                assert($privateKeyRes !== false);
                openssl_private_encrypt(
                    (new AddRsaSha256PrefixAndReturnAsBinary())->__invoke($ciphertext),
                    $encrypted,
                    $privateKeyRes,
                    OPENSSL_PKCS1_PADDING,
                );

                return $encrypted;
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

            $xpathElement = static function (DOMDocument $xml, string $xpathExpression): string {
                $xpath = new DOMXpath($xml);

                $nodes  = $xpath->query($xpathExpression);
                $result = '';

                if (! ($nodes instanceof DOMNodeList)) {
                    return $result;
                }

                foreach ($nodes as $node) {
                    assert($node instanceof DOMNode);
                    $result .= $node->C14N();
                }

                return trim($result);
            };

            $digestValue    = $findElement($xml, 'DigestValue')->nodeValue ?? '';
            $signatureValue = $findElement($xml, 'SignatureValue')->nodeValue ?? '';
            self::assertTrue(
                $digestOk($xpathElement($xml, "//*[@authenticate='true']"), $digestValue),
                $digestDump($xpathElement($xml, "//*[@authenticate='true']"), $digestValue),
            );
            self::assertTrue($signatureOk($findElement($xml, 'SignedInfo')->C14N(), $signatureValue));
        };
    }
}
