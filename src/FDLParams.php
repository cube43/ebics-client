<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use DateTimeImmutable;
use RuntimeException;

use function strlen;
use function strtoupper;

/** @psalm-immutable */
class FDLParams
{
    private string $fileFormat;
    private string $countryCode;
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;

    public function __construct(string $fileFormat, string $countryCode, DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        if (empty($fileFormat)) {
            throw new RuntimeException('fileFormat is empty');
        }

        if (strlen($countryCode) !== 2) {
            throw new RuntimeException('countryCode must be [A-Z]{2,2}');
        }

        $this->fileFormat  = $fileFormat;
        $this->countryCode = strtoupper($countryCode);
        $this->startDate   = $startDate;
        $this->endDate     = $endDate;
    }

    public function fileFormat(): string
    {
        return $this->fileFormat;
    }

    public function countryCode(): string
    {
        return $this->countryCode;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }
}
