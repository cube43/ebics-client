<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * UserUnknownException used for 091003 EBICS error
 */
class UserUnknownException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091003',
            $responseMessage,
            'The identification and authentication signature of the technical user is ' .
            'successfully verified but the non-technical subscriber is not known to the bank.',
        );
    }
}
