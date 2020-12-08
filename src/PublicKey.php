<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use Cube43\Component\Ebics\Crypt\ExponentAndModulus;
use RuntimeException;

/**
 * @internal
 *
 * @psalm-immutable
 */
class PublicKey implements Key
{
    private string $value;
    private ExponentAndModulus $exponentAndModulus;
    private ?string $rsaPassword;

    public function __construct(string $value, ?string $rsaPassword = null)
    {
        if (empty($value)) {
            throw new RuntimeException('public key is empty');
        }

        $this->value              = $value;
        $this->rsaPassword        = $rsaPassword;
        $this->exponentAndModulus = new ExponentAndModulus($this->value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function password(): string
    {
        if ($this->rsaPassword === null) {
            throw new RuntimeException('no password');
        }

        return $this->rsaPassword;
    }

    public function getExponentAndModulus(): ExponentAndModulus
    {
        return $this->exponentAndModulus;
    }
}
