<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use RuntimeException;

/**
 * @internal
 */
class PrivateKey
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new RuntimeException('private key is empty');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
