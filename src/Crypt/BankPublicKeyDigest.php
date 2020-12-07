<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Crypt;

use Fezfez\Ebics\BankCertificate;
use phpseclib\Crypt\RSA;
use RuntimeException;

use function base64_encode;
use function hash;
use function ltrim;
use function sprintf;
use function strlen;

/**
 * @internal
 */
class BankPublicKeyDigest
{
    public function __invoke(BankCertificate $certificate): string
    {
        $publicKey = new RSA();

        if ($publicKey->loadKey($certificate->getPublicKey()) === false) {
            throw new RuntimeException('unable to load key');
        }

        return base64_encode(hash('sha256', $this->generateDigest($publicKey), true));
    }

    private function generateDigest(RSA $publicKey): string
    {
        $exponent = $publicKey->exponent->toHex(true);
        $modulus  = $publicKey->modulus->toHex(true);

        // If key was formed incorrect with Modulus and Exponent mismatch, then change the place of key parts.
        if (strlen($exponent) > strlen($modulus)) {
            return sprintf('%s %s', ltrim($modulus, '0'), ltrim($exponent, '0'));
        }

        return sprintf('%s %s', ltrim($exponent, '0'), ltrim($modulus, '0'));
    }
}
