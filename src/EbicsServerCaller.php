<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use Cube43\Component\Ebics\Exceptions\EbicsResponseException;

interface EbicsServerCaller
{
    /**
     * @param string[] $expectedReturnCode
     *
     * @throws EbicsResponseException
     */
    public function __invoke(string $request, BankInfo $bank, array $expectedReturnCode = ['000000']): string;
}
