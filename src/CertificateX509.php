<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use ErrorException;
use phpseclib\File\X509;
use RuntimeException;

use function array_map;
use function array_shift;
use function base64_encode;
use function chunk_split;
use function hash;
use function hex2bin;
use function implode;
use function is_array;
use function openssl_x509_fingerprint;
use function preg_match;
use function str_contains;
use function str_split;
use function strtoupper;
use function wordwrap;

class CertificateX509
{
    private readonly X509 $x509;

    public function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new RuntimeException('x509 key is empty');
        }

        $this->x509 = new X509();

        if ($this->isHexadecimal($value)) {
            $value2Bin = hex2bin($value);
            if ($value2Bin === false) {
                throw new RuntimeException('x509 value is not DER format');
            }

            $this->x509->loadX509($value2Bin);
        } else {
            $this->x509->loadX509($value);
        }
    }

    private function isHexadecimal(string $str): bool
    {
        // Autoriser uniquement 0-9, a-f, A-F, espaces et ":"
        return (bool) preg_match('/^[0-9a-fA-F:\s]+$/', $str);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function fingerprint(): string
    {
        $contentCert = $this->value;
        // Si pas BEGIN CERTIFICATE alors c'est un encodage DER, il faut le transformer en PEM
        if (! str_contains($contentCert, '-----BEGIN CERTIFICATE-----')) {
            $contentCert = "-----BEGIN CERTIFICATE-----\n"
                . chunk_split(base64_encode($contentCert), 64, "\n")
                . "-----END CERTIFICATE-----\n";
        }

        $digest = strtoupper(self::opensslX509Fingerprint($contentCert, 'sha256'));

        $digests = str_split($digest, 16);
        $digests = array_map(static function ($digest) {
            return wordwrap($digest, 2, ' ', true);
        }, $digests);

        return implode("\n", $digests);
    }

    /**
     * @deprecated Use always fingerprint method
     */
    public function digest(): string
    {
        $digest  = strtoupper(hash('sha256', $this->value, false));
        $digests = str_split($digest, 16);
        $digests = array_map(static function ($digest) {
            return wordwrap($digest, 2, ' ', true);
        }, $digests);

        return implode("\n", $digests);
    }

    /** @internal */
    public function getSerialNumber(): string
    {
        $certificateSerialNumber = $this->x509->currentCert['tbsCertificate']['serialNumber'];

        return $certificateSerialNumber->toString();
    }

    /** @return mixed[] */
    public function getCurrentCert(): array
    {
        return $this->x509->currentCert;
    }

    /** @internal */
    public function getInsurerName(): string
    {
        $certificateInsurerName = $this->x509->getIssuerDNProp('id-at-commonName');

        if (! is_array($certificateInsurerName) || empty($certificateInsurerName)) {
            throw new RuntimeException('unable to get id-at-commonName from certificate');
        }

        return array_shift($certificateInsurerName);
    }

    private static function opensslX509Fingerprint(string $certificate, string $digestAlgo = 'sha1', bool $binary = false): string
    {
        $safeResult = openssl_x509_fingerprint($certificate, $digestAlgo, $binary);
        if ($safeResult === false) {
            throw new ErrorException('An error occured');
        }

        return $safeResult;
    }
}
