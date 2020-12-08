<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use RuntimeException;

use function bin2hex;
use function is_array;
use function is_string;
use function openssl_pkey_get_details;
use function Safe\openssl_pkey_get_public;
use function Safe\sprintf;

/**
 * @internal
 */
class ExponentAndModulus
{
    /** @var array<array-key, mixed>|null */
    private ?array $key;
    private string $value;

    public function __construct(string $key)
    {
        $this->key   = null;
        $this->value = $key;
    }

    private function loadKey(string $value): string
    {
        if ($this->key === null) {
            $loadKey = openssl_pkey_get_details(openssl_pkey_get_public($this->value));

            if (! is_array($loadKey) || ! is_array($loadKey['rsa'])) {
                throw new RuntimeException('cant load key');
            }

            $this->key = $loadKey['rsa'];
        }

        if (! is_string($this->key[$value])) {
            throw new RuntimeException(sprintf('unable to get key "%s"', $value));
        }

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
