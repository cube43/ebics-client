<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InternalErrorException used for 061099 EBICS error
 */
class InternalErrorException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '061099',
            $responseMessage,
            'An internal error occurred when processing an EBICS request.',
        );
    }
}
