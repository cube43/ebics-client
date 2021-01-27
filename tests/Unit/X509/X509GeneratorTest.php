<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\CertificateType;
use Cube43\Component\Ebics\Crypt\GenerateCertificat;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\X509\DefaultX509OptionGenerator;
use Cube43\Component\Ebics\X509\X509CertificatOptionsGenerator;
use Cube43\Component\Ebics\X509\X509Generator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass X509Generator
 */
class X509GeneratorTest extends TestCase
{
    public function testFailOnExtension(): void
    {
        $generateCert = new GenerateCertificat();
        $keyRing      = new KeyRing('helllooo!');

        $certificatE = $generateCert->__invoke(new DefaultX509OptionGenerator(), $keyRing, CertificateType::e());

        $sUT = new X509Generator();

        $tmp = new class implements X509CertificatOptionsGenerator {
            /** @psalm-pure */
            public function getOption(): array
            {
                return [
                    'extensions' => [
                        'arggg' => [],
                    ],
                ];
            }

            /** @psalm-pure */
            public function getStart(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }

            /** @psalm-pure */
            public function getEnd(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Unable to set "arggg" extension with value: array (
)');
        $sUT->__invoke($certificatE->getPrivateKey()->value(), $certificatE->getPublicKey()->value(), CertificateType::e(), $tmp);
    }
}
