<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

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
    private X509 $x509;
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new RuntimeException('x509 key is empty');
        }

        $this->x509 = new X509();
        $this->x509->loadX509($value);

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function fingerprint(): string
    {
        $digest  = strtoupper(openssl_x509_fingerprint($this->value, 'sha256'));
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

    /**
     * @internal
     */
    public function getSerialNumber(): string
    {
        $certificateSerialNumber = $this->x509->currentCert['tbsCertificate']['serialNumber'];

        return $certificateSerialNumber->toString();
    }

    /**
     * @internal
     */
    public function getInsurerName(): string
    {
        $certificateInsurerName = $this->x509->getIssuerDNProp('id-at-commonName');

        if (! is_array($certificateInsurerName) || empty($certificateInsurerName)) {
            throw new RuntimeException('unable to get id-at-commonName from certificate');
        }

        return array_shift($certificateInsurerName);
    }
}
