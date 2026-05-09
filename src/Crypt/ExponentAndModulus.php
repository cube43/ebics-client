<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use phpseclib3\Crypt\Common\PublicKey;

/** @internal */
class ExponentAndModulus
{
    /** @var array{e: \phpseclib3\Math\BigInteger, n: \phpseclib3\Math\BigInteger} */
    private readonly array $components;

    public function __construct(PublicKey $key)
    {
        /** @var array{e: \phpseclib3\Math\BigInteger, n: \phpseclib3\Math\BigInteger} $components */
        $components       = $key->toString('Raw');
        $this->components = $components;
    }

    public function getExponent(): string
    {
        return $this->components['e']->toBytes();
    }

    public function getModulus(): string
    {
        return $this->components['n']->toBytes();
    }
}
