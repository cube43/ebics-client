<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

interface EbicsServerCaller
{
    /** @param string[] $expectedReturnCode */
    public function __invoke(string $request, BankInfo $bank, array $expectedReturnCode = ['000000']): string;
}
