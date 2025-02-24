<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use ErrorException;
use phpseclib\File\X509;
use RuntimeException;
use Throwable;

use function array_map;
use function array_shift;
use function base64_encode;
use function chunk_split;
use function hash;
use function implode;
use function is_array;
use function openssl_x509_fingerprint;
use function str_split;
use function strtoupper;
use function wordwrap;

class CertificateX509
{
    private readonly X509 $x509;

    public function __construct(private string $value)
    {
        if (empty($value)) {
            throw new RuntimeException('x509 key is empty');
        }

        $this->x509 = new X509();
        $this->x509->loadX509($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function fingerprint(): string
    {
        try {
            $digest = strtoupper(self::opensslX509Fingerprint($this->value, 'sha256'));
        } catch (Throwable) {
            $under  = "-----BEGIN CERTIFICATE-----\r\n" . chunk_split(base64_encode($this->value), 64) . '-----END CERTIFICATE-----';
            $digest = strtoupper(self::opensslX509Fingerprint($under, 'sha256'));
        }

        $digests = str_split($digest, 16);
        $digests = array_map(static function ($digest) {
            return wordwrap($digest, 2, ' ', true);
        }, $digests);

        return implode("\n", $digests);
    }

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
