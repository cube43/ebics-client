<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use Cube43\Component\Ebics\Crypt\ExponentAndModulus;
use ErrorException;
use JsonSerializable;
use phpseclib\Crypt\RSA;

use function base64_decode;
use function base64_encode;

class BankCertificate implements JsonSerializable
{
    public function __construct(
        private readonly CertificatType $type,
        private readonly string $publicKey,
        private readonly CertificateX509 $x509,
    ) {
    }

    /** @param array<string, string> $bankCertificateX */
    public static function fromArray(array $bankCertificateX): self
    {
        return new self(
            CertificatType::fromString($bankCertificateX['type']),
            self::base64Decode($bankCertificateX['public']),
            new CertificateX509(self::base64Decode($bankCertificateX['content'])),
        );
    }

    private static function base64Decode(string $string): string
    {
        $safeResult = base64_decode($string);
        if ($safeResult === false) {
            throw new ErrorException('An error occured');
        }

        return $safeResult;
    }

    public function getCertificatType(): CertificatType
    {
        return $this->type;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getCertificatX509(): CertificateX509
    {
        return $this->x509;
    }

    public function getPublicKeyDetails(): ExponentAndModulus
    {
        $rsa = new RSA();
        $rsa->setPublicKey($this->publicKey);

        return new ExponentAndModulus($rsa);
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value(),
            'public' => base64_encode($this->publicKey),
            'content' => base64_encode($this->x509->value()),
        ];
    }
}
