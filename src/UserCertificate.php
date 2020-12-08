<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use JsonSerializable;

use function base64_encode;
use function Safe\base64_decode;

class UserCertificate implements JsonSerializable
{
    private CertificateType $type;
    private PublicKey $publicKey;
    private PrivateKey $privateKey;
    private CertificateX509 $x509;

    public function __construct(CertificateType $type, PublicKey $publicKey, PrivateKey $privateKey, CertificateX509 $x509)
    {
        $this->type       = $type;
        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;
        $this->x509       = $x509;
    }

    /**
     * @param array<string, string> $certificat
     */
    public static function fromArray(array $certificat, string $rsaPassword): self
    {
        return new self(
            CertificateType::fromString($certificat['type']),
            new PublicKey(base64_decode($certificat['public']), $rsaPassword),
            new PrivateKey(base64_decode($certificat['private']), $rsaPassword),
            new CertificateX509(base64_decode($certificat['content']))
        );
    }

    public function getCertificatType(): CertificateType
    {
        return $this->type;
    }

    public function getPublicKey(): PublicKey
    {
        return $this->publicKey;
    }

    public function getPrivateKey(): PrivateKey
    {
        return $this->privateKey;
    }

    public function getCertificatX509(): CertificateX509
    {
        return $this->x509;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value(),
            'public' => base64_encode($this->publicKey->value()),
            'private' => base64_encode($this->privateKey->value()),
            'content' => base64_encode($this->x509->value()),
        ];
    }
}
