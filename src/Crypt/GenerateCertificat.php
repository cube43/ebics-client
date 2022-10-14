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
use phpseclib\Crypt\RSA;
use RuntimeException;

use function array_key_exists;
use function sprintf;

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
        $rsa = new RSA();
        $rsa->setPublicKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
        $rsa->setPrivateKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
        $rsa->setHash('sha256');
        $rsa->setMGFHash('sha256');
        $rsa->setPassword($keyring->getPassword());

        $keys = $rsa->createKey(2048);

        if (empty($keys)) {
            throw new RuntimeException(sprintf('key "publickey" does not exist for certificat type "%s"', $type->value()));
        }

        if (! array_key_exists('publickey', $keys) || empty($keys['publickey'])) {
            throw new RuntimeException(sprintf('key "publickey" does not exist for certificat type "%s"', $type->value()));
        }

        if (! array_key_exists('privatekey', $keys) || empty($keys['privatekey'])) {
            throw new RuntimeException(sprintf('key "privatekey" does not exist for certificat type "%s"', $type->value()));
        }

        $privateKey = new RSA();
        $privateKey->loadKey($keys['privatekey']);

        $publicKey = new RSA();
        $publicKey->loadKey($keys['publickey']);
        $publicKey->setPublicKey();

        return new UserCertificate(
            $type,
            $keys['publickey'],
            new PrivateKey($keys['privatekey']),
            new CertificateX509($this->x509Generator->__invoke($privateKey, $publicKey, $type, $x509CertificatOptionsGenerator)),
        );
    }
}
