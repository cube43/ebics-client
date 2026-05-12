<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use phpseclib3\Crypt\Common\PublicKey;
use phpseclib3\Math\BigInteger;

/** @internal */
class ExponentAndModulus
{
    /** @var array{e: BigInteger, n: BigInteger} */
    private readonly array $components;

    public function __construct(PublicKey $key)
    {
        /** @var array{e: BigInteger, n: BigInteger} $components */
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
