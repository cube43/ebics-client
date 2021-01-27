<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\CertificateType;
use Cube43\Component\Ebics\CertificateX509;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\PrivateKey;
use Cube43\Component\Ebics\PublicKey;
use Cube43\Component\Ebics\UserCertificate;
use Cube43\Component\Ebics\X509\X509CertificatOptionsGenerator;
use Cube43\Component\Ebics\X509\X509Generator;
use phpseclib\Crypt\RSA;
use RuntimeException;

use function array_key_exists;
use function is_array;
use function sprintf;

/**
 * @internal
 *
 * @psalm-pure
 */
class GenerateCertificat
{
    private X509Generator $x509Generator;

    public function __construct(?X509Generator $x509Generator = null)
    {
        $this->x509Generator = $x509Generator ?? new X509Generator();
    }

    public function __invoke(X509CertificatOptionsGenerator $x509CertificatOptionsGenerator, KeyRing $keyring, CertificateType $type): UserCertificate
    {
        $rsa = new RSA();
        $rsa->setPublicKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
        $rsa->setPrivateKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
        $rsa->setHash('sha256');
        $rsa->setMGFHash('sha256');
        $rsa->setPassword($keyring->getRsaPassword());

        $keys = $rsa->createKey(2048);

        if (! is_array($keys)) {
            throw new RuntimeException(sprintf('key "publickey" does not exist for certificat type "%s"', $type->value()));
        }

        if (! array_key_exists('publickey', $keys) || empty($keys['publickey'])) {
            throw new RuntimeException(sprintf('key "publickey" does not exist for certificat type "%s"', $type->value()));
        }

        if (! array_key_exists('privatekey', $keys) || empty($keys['privatekey'])) {
            throw new RuntimeException(sprintf('key "privatekey" does not exist for certificat type "%s"', $type->value()));
        }

        return new UserCertificate(
            $type,
            new PublicKey($keys['publickey'], $keyring->getRsaPassword()),
            new PrivateKey($keys['privatekey'], $keyring->getRsaPassword()),
            new CertificateX509($this->x509Generator->__invoke($keys['privatekey'], $keys['publickey'], $type, $x509CertificatOptionsGenerator))
        );
    }
}
