<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\Version;
use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    public function testVersionConstruct(): void
    {
        self::assertSame('H003', Version::v24()->value());
        self::assertSame('H004', Version::v25()->value());
        self::assertSame('H005', Version::v30()->value());
        self::assertTrue(Version::v24()->is(Version::v24()));
        self::assertTrue(Version::v25()->is(Version::v25()));
        self::assertTrue(Version::v30()->is(Version::v30()));
        self::assertFalse(Version::v30()->is(Version::v24()));
    }
}
