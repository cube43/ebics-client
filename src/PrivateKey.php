<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use RuntimeException;

/** @internal */
class PrivateKey
{
    public function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new RuntimeException('private key is empty');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
