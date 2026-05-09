<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\BankCertificate;
use phpseclib3\Crypt\Common\PublicKey;
use phpseclib3\Crypt\RSA;
use RuntimeException;

use function base64_encode;
use function hash;
use function ltrim;
use function sprintf;
use function strlen;

class BankPublicKeyDigest
{
    public function __invoke(BankCertificate $certificate): string
    {
        $publicKey = RSA::loadPublicKey($certificate->getPublicKey());

        return base64_encode(hash('sha256', $this->generateDigest($publicKey), true));
    }

    private function generateDigest(PublicKey $publicKey): string
    {
        /** @var array{e: \phpseclib3\Math\BigInteger, n: \phpseclib3\Math\BigInteger} */
        $raw      = $publicKey->toString('Raw');
        $exponent = $raw['e']->toHex();
        $modulus  = $raw['n']->toHex();

        // If key was formed incorrect with Modulus and Exponent mismatch, then change the place of key parts.
        if (strlen($exponent) > strlen($modulus)) {
            return sprintf('%s %s', ltrim($modulus, '0'), ltrim($exponent, '0'));
        }

        return sprintf('%s %s', ltrim($exponent, '0'), ltrim($modulus, '0'));
    }
}
