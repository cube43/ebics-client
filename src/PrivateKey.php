<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use RuntimeException;

/**
 * @internal
 *
 * @psalm-immutable
 */
class PrivateKey implements Key
{
    private string $value;
    private string $password;

    public function __construct(string $value, string $password)
    {
        if (empty($value)) {
            throw new RuntimeException('private key is empty');
        }

        $this->value    = $value;
        $this->password = $password;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function value(): string
    {
        return $this->value;
    }
}
