<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\Unit;

use Fezfez\Ebics\OrderDataEncrypted;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OrderDataEncrypted
 */
class OrderDataEncryptedTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new OrderDataEncrypted('test', 'test2');

        self::assertSame('test', $sUT->getOrderData());
        self::assertSame('test2', $sUT->getTransactionKey());
    }
}
