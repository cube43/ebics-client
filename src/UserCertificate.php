<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

use Fezfez\Ebics\Crypt\ExponentAndModulus;
use JsonSerializable;
use phpseclib\Crypt\RSA;

use function base64_encode;
use function Safe\base64_decode;

class UserCertificate implements JsonSerializable
{
    private CertificatType $type;
    private string $publicKey;
    private PrivateKey $privateKey;
    private CertificateX509 $x509;

    public function __construct(CertificatType $type, string $publicKey, PrivateKey $privateKey, CertificateX509 $x509)
    {
        $this->type       = $type;
        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;
        $this->x509       = $x509;
    }

    /**
     * @param array<string, string> $bankCertificateX
     */
    public static function fromArray(array $bankCertificateX): self
    {
        return new self(
            CertificatType::fromString($bankCertificateX['type']),
            base64_decode($bankCertificateX['public']),
            new PrivateKey(base64_decode($bankCertificateX['private'])),
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

    public function getPrivateKey(): PrivateKey
    {
        return $this->privateKey;
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
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value(),
            'public' => base64_encode($this->publicKey),
            'private' => base64_encode($this->privateKey->value()),
            'content' => base64_encode($this->x509->value()),
        ];
    }
}
