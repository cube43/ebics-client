<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * KeymgmtKeylengthErrorSignatureException used for 091204 EBICS error
 */
class KeymgmtKeylengthErrorSignatureException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091204',
            $responseMessage,
            'When processing an INI request, the order data contains ' .
            'an bank-technical key of inadmissible length.',
        );
    }
}
