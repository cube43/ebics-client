<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use phpseclib\Crypt\RSA;

/** @internal */
class ExponentAndModulus
{
    public function __construct(private readonly RSA $rsa)
    {
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
