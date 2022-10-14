<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

class Version
{
    private const V24 = 'H003';
    private const V25 = 'H004';
    private const V30 = 'H005';

    private function __construct(private readonly string $value)
    {
    }

    public static function v24(): self
    {
        return new self(self::V24);
    }

    public static function v25(): self
    {
        return new self(self::V25);
    }

    public static function v30(): self
    {
        return new self(self::V30);
    }

    public function is(Version $version): bool
    {
        return $version->value === $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
