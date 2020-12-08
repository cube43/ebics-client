<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\BankCertificate;

use function base64_encode;
use function hash;
use function ltrim;
use function sprintf;
use function strlen;

/**
 * @internal
 *
 * @psalm-pure
 */
final class BankPublicKeyDigest
{
    public function __invoke(BankCertificate $certificate): string
    {
        return base64_encode(hash('sha256', $this->generateDigest($certificate->getPublicKey()->getExponentAndModulus()), true));
    }

    private function generateDigest(ExponentAndModulus $exponentAndModulus): string
    {
        $exponent = $exponentAndModulus->getExponentToHex();
        $modulus  = $exponentAndModulus->getModulusToHex();

        // If key was formed incorrect with Modulus and Exponent mismatch, then change the place of key parts.
        if (strlen($exponent) > strlen($modulus)) {
            return sprintf('%s %s', ltrim($modulus, '0'), ltrim($exponent, '0'));
        }

        return sprintf('%s %s', ltrim($exponent, '0'), ltrim($modulus, '0'));
    }
}
