<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\Unit;

use DateTimeImmutable;
use Fezfez\Ebics\FDLParams;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass FDLParams
 */
class FDLParamsTest extends TestCase
{
    public function testFailOnEmptyFileFormat(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('fileFormat is empty');

        $sUT = new FDLParams('', 'test', new DateTimeImmutable(), new DateTimeImmutable());
    }

    public function testFailOnEmptyCountryCode(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('countryCode is empty');

        $sUT = new FDLParams('test', '', new DateTimeImmutable(), new DateTimeImmutable());
    }

    public function testOk(): void
    {
        $start = self::createMock(DateTimeImmutable::class);
        $end   = self::createMock(DateTimeImmutable::class);
        $sUT   = new FDLParams('fileF', 'FR', $start, $end);

        self::assertSame('fileF', $sUT->fileFormat());
        self::assertSame('FR', $sUT->countryCode());
        self::assertSame($start, $sUT->getStartDate());
        self::assertSame($end, $sUT->getEndDate());
    }
}
