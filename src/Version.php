<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

class Version
{
    private const V24 = 'H003';
    private const V25 = 'H004';
    private const V30 = 'H005';
    private const V24_SVIRIN = 'VERSION_24';
    private const V25_SVIRIN = 'VERSION_25';
    private const V30_SVIRIN = 'VERSION_30';

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

    public static function v24Svirin(): self
    {
        return new self(self::V24_SVIRIN);
    }

    public static function v25Svirin(): self
    {
        return new self(self::V25_SVIRIN);
    }

    public static function v30Svirin(): self
    {
        return new self(self::V30_SVIRIN);
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
