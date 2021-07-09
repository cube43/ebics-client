<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * KeymgmtUnsupportedVersionAuthenticationException used for 091202 EBICS error
 */
class KeymgmtUnsupportedVersionAuthenticationException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091202',
            $responseMessage,
            'When processing an HIA request, the order data contains an inadmissible ' .
            'version of the identification and authentication signature process.'
        );
    }
}
