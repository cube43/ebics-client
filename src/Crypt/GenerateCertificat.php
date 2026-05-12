<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\CertificateX509;
use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\PrivateKey;
use Cube43\Component\Ebics\UserCertificate;
use Cube43\Component\Ebics\X509\X509CertificatOptionsGenerator;
use Cube43\Component\Ebics\X509\X509Generator;
use phpseclib3\Crypt\RSA;

/** @internal */
class GenerateCertificat
{
    private readonly X509Generator $x509Generator;

    public function __construct(X509Generator|null $x509Generator = null)
    {
        $this->x509Generator = $x509Generator ?? new X509Generator();
    }

    public function __invoke(X509CertificatOptionsGenerator $x509CertificatOptionsGenerator, KeyRing $keyring, CertificatType $type): UserCertificate
    {
        $privateKey = RSA::createKey(2048);
        $publicKey  = $privateKey->getPublicKey();

        $privateKeyString = $privateKey->withPassword($keyring->getPassword())->toString('PKCS1');
        $publicKeyString  = $publicKey->toString('PKCS1');

        return new UserCertificate(
            $type,
            $publicKeyString,
            new PrivateKey($privateKeyString),
            new CertificateX509($this->x509Generator->__invoke($privateKey, $publicKey, $type, $x509CertificatOptionsGenerator)),
        );
    }
}
