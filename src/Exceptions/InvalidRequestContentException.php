<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidRequestContentException used for 091113 EBICS error
 */
class InvalidRequestContentException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091113',
            $responseMessage,
            'The EBICS request does not conform to the XML schema definition specified for individual requests.'
        );
    }
}
