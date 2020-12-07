<?php

declare(strict_types=1);

namespace Fezfez\Ebics\X509;

use DateTimeImmutable;

interface X509CertificatOptionsGenerator
{
    /**
     * @return array<string, mixed>
     */
    public function getOption(): array;

    public function getStart(): DateTimeImmutable;

    public function getEnd(): DateTimeImmutable;
}
