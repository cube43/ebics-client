<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use DateTimeImmutable;
use RuntimeException;

class FDLParams
{
    public function __construct(
        private readonly string $fileFormat,
        private readonly string $countryCode,
        private readonly DateTimeImmutable $startDate,
        private readonly DateTimeImmutable $endDate,
    ) {
        if (empty($fileFormat)) {
            throw new RuntimeException('fileFormat is empty');
        }

        if (empty($countryCode)) {
            throw new RuntimeException('countryCode is empty');
        }
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
