<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\FDLParams;
use DateTimeImmutable;
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

        new FDLParams('', 'test', new DateTimeImmutable(), new DateTimeImmutable());
    }

    public function testFailOnEmptyCountryCode(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('countryCode must be [A-Z]{2,2}');

        new FDLParams('test', '', new DateTimeImmutable(), new DateTimeImmutable());
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
