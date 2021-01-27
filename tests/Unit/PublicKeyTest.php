<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\PublicKey;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass PublicKey
 */
class PublicKeyTest extends TestCase
{
    public function testEmptyFail(): void
    {
        self::expectException(RuntimeException::class);
        new PublicKey('');
    }

    public function testOk(): void
    {
        $sUT = new PublicKey('test', 'test2');

        self::assertSame('test', $sUT->value());
        self::assertSame('test2', $sUT->password());
        self::assertNotEmpty($sUT->getExponentAndModulus());
    }

    public function testEmptyPasswordFail(): void
    {
        $sUT = new PublicKey('test');

        self::expectException(RuntimeException::class);

        $sUT->password();
    }
}
