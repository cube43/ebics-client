<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit\Crypt;

use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\Crypt\GenerateCertificat;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\X509\DefaultX509OptionGenerator;
use Cube43\Component\Ebics\X509\EbicsX509FormatEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function base64_encode;
use function chunk_split;
use function openssl_pkey_get_details;
use function openssl_pkey_get_public;
use function openssl_x509_parse;
use function str_starts_with;

use const OPENSSL_KEYTYPE_RSA;

class GenerateCertificatTest extends TestCase
{
    /** @return iterable<string, array{EbicsX509FormatEnum, CertificatType}> */
    public static function provideFormatAndType(): iterable
    {
        foreach ([EbicsX509FormatEnum::PEM, EbicsX509FormatEnum::DER] as $format) {
            yield $format->value . ' A' => [$format, CertificatType::a()];
            yield $format->value . ' X' => [$format, CertificatType::x()];
            yield $format->value . ' E' => [$format, CertificatType::e()];
        }
    }

    /**
     * phpseclib 3 signe en RSASSA-PSS par defaut. Les banques (ex. CIC : 090004 EBICS_INVALID_ORDER_DATA_FORMAT sur INI/HIA)
     * attendent un certificat classique : signature sha256WithRSAEncryption et cle publique rsaEncryption.
     */
    #[DataProvider('provideFormatAndType')]
    public function testCertificatIsNotRsaPss(EbicsX509FormatEnum $format, CertificatType $type): void
    {
        $options = new class ($format) extends DefaultX509OptionGenerator {
            public function __construct(private readonly EbicsX509FormatEnum $format)
            {
            }

            public function getFormat(): EbicsX509FormatEnum
            {
                return $this->format;
            }
        };

        $certificate = (new GenerateCertificat())->__invoke($options, new KeyRing('password'), $type);

        $value = $certificate->getCertificatX509()->value();
        $pem   = str_starts_with($value, '-----BEGIN') ? $value : "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($value), 64, "\n") . "-----END CERTIFICATE-----\n";

        $parsed = openssl_x509_parse($pem);
        self::assertIsArray($parsed);
        self::assertSame('sha256WithRSAEncryption', $parsed['signatureTypeLN']);

        $publicKey = openssl_pkey_get_public($pem);
        self::assertNotFalse($publicKey);

        $details = openssl_pkey_get_details($publicKey);
        self::assertIsArray($details);
        self::assertSame(OPENSSL_KEYTYPE_RSA, $details['type']);
        self::assertSame(2048, $details['bits']);
    }
}
