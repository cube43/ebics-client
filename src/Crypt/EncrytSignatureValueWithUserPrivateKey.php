<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\Key;
use Cube43\Component\Ebics\KeyRing;
use phpseclib\Crypt\RSA;
use RuntimeException;

use function define;
use function defined;

/**
 * @internal
 *
 * @psalm-pure
 */
final class EncrytSignatureValueWithUserPrivateKey
{
    private AddRsaSha256PrefixAndReturnAsBinary $addRsaSha256PrefixAndReturnAsBinary;

    public function __construct()
    {
        $this->addRsaSha256PrefixAndReturnAsBinary = new AddRsaSha256PrefixAndReturnAsBinary();
    }

    /**
     * @throws RuntimeException
     */
    public function __invoke(KeyRing $keyRing, Key $key, string $hash): string
    {
        $rsa = new RSA();
        $rsa->setPassword($keyRing->getRsaPassword());
        $rsa->loadKey($key->value(), RSA::PRIVATE_FORMAT_PKCS1);

        if (! defined('CRYPT_RSA_PKCS15_COMPAT')) {
            define('CRYPT_RSA_PKCS15_COMPAT', true);
        }

        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $encrypted = $rsa->encrypt($this->addRsaSha256PrefixAndReturnAsBinary->__invoke($hash));

        if (empty($encrypted)) {
            throw new RuntimeException('Incorrect authorization.');
        }

        return $encrypted;
    }
}
