<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Command;

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\FDLParams;
use Cube43\Component\Ebics\KeyRing;

class FDLResponse
{
    public function __construct(
        public readonly BankInfo $bank,
        public readonly KeyRing $keyRing,
        public readonly FDLParams $FDLParams,
        public readonly DOMDocument $serverResponse,
        public readonly string|null $data,
    ) {
    }
}
