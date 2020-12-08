<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use phpseclib\File\X509;
use RuntimeException;

use function array_map;
use function array_shift;
use function hash;
use function implode;
use function is_array;
use function Safe\openssl_x509_fingerprint;
use function str_split;
use function strtoupper;
use function wordwrap;

class CertificateX509
{
    private ?X509 $x509;
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new RuntimeException('x509 key is empty');
        }

        $this->x509  = null;
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function fingerprint(string $hashAlgorithm = 'sha256'): string
    {
        return $this->cleanDigest(openssl_x509_fingerprint($this->value, $hashAlgorithm));
    }

    public function hash(string $hashAlgorithm = 'sha256'): string
    {
        return $this->cleanDigest(hash($hashAlgorithm, $this->value, false));
    }

    private function cleanDigest(string $digest): string
    {
        $digests = str_split(strtoupper($digest), 16);
        $digests = array_map(static fn ($digest) => wordwrap($digest, 2, ' ', true), $digests);

        return implode("\n", $digests);
    }

    private function getX509(): X509
    {
        if ($this->x509 === null) {
            $this->x509 = new X509();
            $this->x509->loadX509($this->value);
        }

        return $this->x509;
    }

    /**
     * @internal
     */
    public function getSerialNumber(): string
    {
        $certificateSerialNumber = $this->getX509()->currentCert['tbsCertificate']['serialNumber'];

        return $certificateSerialNumber->toString();
    }

    /**
     * @internal
     */
    public function getInsurerName(): string
    {
        $certificateInsurerName = $this->getX509()->getIssuerDNProp('id-at-commonName');

        if (! is_array($certificateInsurerName) || empty($certificateInsurerName)) {
            throw new RuntimeException('unable to get id-at-commonName from certificate');
        }

        return array_shift($certificateInsurerName);
    }
}
