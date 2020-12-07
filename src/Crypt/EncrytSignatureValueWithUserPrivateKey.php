<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Crypt;

use Fezfez\Ebics\KeyRing;
use Fezfez\Ebics\PrivateKey;
use phpseclib\Crypt\RSA;
use RuntimeException;

use function define;
use function defined;

/**
 * @internal
 */
class EncrytSignatureValueWithUserPrivateKey
{
    private AddRsaSha256PrefixAndReturnAsBinary $addRsaSha256PrefixAndReturnAsBinary;

    public function __construct()
    {
        $this->addRsaSha256PrefixAndReturnAsBinary = new AddRsaSha256PrefixAndReturnAsBinary();
    }

    /**
     * @throws RuntimeException
     */
    public function __invoke(KeyRing $keyRing, PrivateKey $key, string $hash): string
    {
        $rsa = new RSA();
        $rsa->setPassword($keyRing->getPassword());
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
