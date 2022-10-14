<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * KeymgmtKeylengthErrorAuthenticationException used for 091205 EBICS error
 */
class KeymgmtKeylengthErrorAuthenticationException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091205',
            $responseMessage,
            'When processing an HIA request, the order data contains an identification ' .
            'and authentication key of inadmissible length.',
        );
    }
}
