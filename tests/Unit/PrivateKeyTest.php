<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\PrivateKey;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PrivateKeyTest extends TestCase
{
    public function testEmptyFail(): void
    {
        self::expectException(RuntimeException::class);
        new PrivateKey('');
    }

    public function testOk(): void
    {
        self::assertSame('test', (new PrivateKey('test'))->value());
    }
}
