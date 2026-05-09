<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\PrivateKey;
use RuntimeException;

use function openssl_pkey_get_private;
use function openssl_private_encrypt;

use const OPENSSL_PKCS1_PADDING;

/** @internal */
class EncrytSignatureValueWithUserPrivateKey
{
    private readonly AddRsaSha256PrefixAndReturnAsBinary $addRsaSha256PrefixAndReturnAsBinary;

    public function __construct()
    {
        $this->addRsaSha256PrefixAndReturnAsBinary = new AddRsaSha256PrefixAndReturnAsBinary();
    }

    /** @throws RuntimeException */
    public function __invoke(KeyRing $keyRing, PrivateKey $key, string $hash): string
    {
        $password       = $keyRing->getPassword();
        $privateKeyRes  = openssl_pkey_get_private($key->value(), $password !== '' ? $password : null);

        if ($privateKeyRes === false) {
            throw new RuntimeException('Unable to load private key.');
        }

        $formattedData = $this->addRsaSha256PrefixAndReturnAsBinary->__invoke($hash);

        if (openssl_private_encrypt($formattedData, $encrypted, $privateKeyRes, OPENSSL_PKCS1_PADDING) === false) {
            throw new RuntimeException('Incorrect authorization.');
        }

        return $encrypted;
    }
}
