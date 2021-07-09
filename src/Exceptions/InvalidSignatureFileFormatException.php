<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidSignatureFileFormatException used for 091111 EBICS error
 */
class InvalidSignatureFileFormatException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091111',
            $responseMessage,
            'The submitted electronic signature file does not conform to the defined format.'
        );
    }
}
