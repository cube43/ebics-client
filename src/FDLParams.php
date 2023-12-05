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
        private readonly DateTimeImmutable|null $startDate,
        private readonly DateTimeImmutable|null $endDate,
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

    public function getStartDate(): DateTimeImmutable|null
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable|null
    {
        return $this->endDate;
    }
}
