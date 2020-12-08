<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use Cube43\Component\Ebics\Crypt\ExponentAndModulus;
use JsonSerializable;
use phpseclib\Crypt\RSA;

use function base64_encode;
use function Safe\base64_decode;

class BankCertificate implements JsonSerializable
{
    private CertificatType $type;
    private string $publicKey;
    private CertificateX509 $x509;

    public function __construct(CertificatType $type, string $publicKey, CertificateX509 $x509)
    {
        $this->type      = $type;
        $this->publicKey = $publicKey;
        $this->x509      = $x509;
    }

    /**
     * @param array<string, string> $bankCertificateX
     */
    public static function fromArray(array $bankCertificateX): self
    {
        return new self(
            CertificatType::fromString($bankCertificateX['type']),
            base64_decode($bankCertificateX['public']),
            new CertificateX509(base64_decode($bankCertificateX['content']))
        );
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

    /**
     * @return array<string, string>
     *
     * @psalm-return array{
     *  type: string,
     *  public: string,
     *  content: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value(),
            'public' => base64_encode($this->publicKey),
            'content' => base64_encode($this->x509->value()),
        ];
    }
}
