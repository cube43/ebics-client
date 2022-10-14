<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidRequestException used for 061002 EBICS error
 */
class InvalidRequestException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '061002',
            $responseMessage,
            'The received EBICS XML message does not conform to the EBICS specifications.',
        );
    }
}
