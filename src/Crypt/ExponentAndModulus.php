<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use RuntimeException;

use function bin2hex;
use function is_array;
use function openssl_pkey_get_details;
use function Safe\openssl_pkey_get_public;

/**
 * @internal
 */
class ExponentAndModulus
{
    /** @var array<string, string>|null */
    private ?array $key;
    private string $value;

    public function __construct(string $key)
    {
        $this->key   = null;
        $this->value = $key;
    }

    private function loadKey(string $value): string
    {
        if ($this->key !== null) {
            return $this->key[$value];
        }

        $loadKey = openssl_pkey_get_details(openssl_pkey_get_public($this->value));

        if (! is_array($loadKey)) {
            throw new RuntimeException('cant load key');
        }

        $this->key = $loadKey['rsa'];

        return $this->key[$value];
    }

    public function getExponent(): string
    {
        return $this->loadKey('e');
    }

    public function getExponentToHex(): string
    {
        return bin2hex($this->loadKey('e'));
    }

    public function getModulus(): string
    {
        return $this->loadKey('n');
    }

    public function getModulusToHex(): string
    {
        return bin2hex($this->loadKey('n'));
    }
}
