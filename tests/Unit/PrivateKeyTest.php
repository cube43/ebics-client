<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\PrivateKey;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass PrivateKey
 */
class PrivateKeyTest extends TestCase
{
    public function testEmptyFail(): void
    {
        self::expectException(RuntimeException::class);
        new PrivateKey('', 'test');
    }

    public function testOk(): void
    {
        $sUT = new PrivateKey('test', 'test2');

        self::assertSame('test', $sUT->value());
        self::assertSame('test2', $sUT->password());
    }
}
