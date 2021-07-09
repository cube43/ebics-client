<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * AuthenticationFailedException used for 061001 EBICS error
 */
class AuthenticationFailedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '061001',
            $responseMessage,
            'The bank is unable to verify the identification and authentication signature of an EBICS request.'
        );
    }
}
