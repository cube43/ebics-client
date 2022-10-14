<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * KeymgmtUnsupportedVersionSignatureException used for 091201 EBICS error
 */
class KeymgmtUnsupportedVersionSignatureException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091201',
            $responseMessage,
            'When processing an INI request, the order data contains an inadmissible ' .
            'version of the bank-technical signature process.',
        );
    }
}
