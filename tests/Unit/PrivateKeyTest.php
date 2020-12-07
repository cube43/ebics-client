<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\Unit;

use Fezfez\Ebics\PrivateKey;
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
