<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use phpseclib\Crypt\RSA;

/**
 * @internal
 */
class ExponentAndModulus
{
    private RSA $rsa;

    public function __construct(RSA $rsa)
    {
        $this->rsa = $rsa;
    }

    public function getExponent(): string
    {
        return $this->rsa->exponent->toBytes();
    }

    public function getModulus(): string
    {
        return $this->rsa->modulus->toBytes();
    }
}
