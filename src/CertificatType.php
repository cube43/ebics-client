<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

use RuntimeException;

use function in_array;

/**
 * @internal
 */
class CertificatType
{
    private string $type;

    private function __construct(string $type)
    {
        if (! in_array($type, ['A', 'E', 'X'])) {
            throw new RuntimeException('unknow certificat type');
        }

        $this->type = $type;
    }

    public static function a(): self
    {
        return new self('A');
    }

    public static function e(): self
    {
        return new self('E');
    }

    public static function x(): self
    {
        return new self('X');
    }

    public function is(self $type): bool
    {
        return $this->value() === $type->value();
    }

    public static function fromString(string $type): self
    {
        return new self($type);
    }

    public function value(): string
    {
        return $this->type;
    }

    public function toString(): string
    {
        if ($this->type === 'X') {
            return 'X002';
        }

        if ($this->type === 'E') {
            return 'E002';
        }

        return 'A006';
    }

    public function getHash(): string
    {
        return 'SHA-256';
    }
}
