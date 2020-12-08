<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use JsonSerializable;

use function base64_encode;
use function Safe\base64_decode;

class BankCertificate implements JsonSerializable
{
    private CertificateType $type;
    private PublicKey $publicKey;
    private CertificateX509 $x509;

    public function __construct(CertificateType $type, PublicKey $publicKey, CertificateX509 $x509)
    {
        $this->type      = $type;
        $this->publicKey = $publicKey;
        $this->x509      = $x509;
    }

    /**
     * @param array<string, string> $certificate
     */
    public static function fromArray(array $certificate, string $rsaPassword): self
    {
        return new self(
            CertificateType::fromString($certificate['type']),
            new PublicKey(base64_decode($certificate['public']), $rsaPassword),
            new CertificateX509(base64_decode($certificate['content']))
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
            'public' => base64_encode($this->publicKey->value()),
            'content' => base64_encode($this->x509->value()),
        ];
    }
}
