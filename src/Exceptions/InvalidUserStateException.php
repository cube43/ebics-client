<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidUserStateException used for 091004 EBICS error
 */
class InvalidUserStateException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091004',
            $responseMessage,
            'The identification and authentication signature of the technical user ' .
            'is successfully verified and the non-technical subscriber is known to the bank, ' .
            'but the user is not in a ’Ready’ state.',
        );
    }
}
