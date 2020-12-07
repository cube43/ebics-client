<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\Unit;

use Fezfez\Ebics\BankInfo;
use Fezfez\Ebics\Version;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass BankInfo
 */
class BankTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new BankInfo('test', 'test2', Version::v24(), 'hello', 'ehg!');

        self::assertSame('test', $sUT->getHostId());
        self::assertSame('test2', $sUT->getUrl());
        self::assertSame(Version::v24()->value(), $sUT->getVersion()->value());
        self::assertTrue($sUT->isCertified());
        self::assertSame('hello', $sUT->getPartnerId());
        self::assertSame('ehg!', $sUT->getUserId());
    }
}
