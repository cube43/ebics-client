<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

use DateTimeImmutable;
use RuntimeException;

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

        if (empty($countryCode)) {
            throw new RuntimeException('countryCode is empty');
        }

        $this->fileFormat  = $fileFormat;
        $this->countryCode = $countryCode;
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
