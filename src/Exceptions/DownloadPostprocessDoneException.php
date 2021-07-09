<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * DownloadPostprocessDoneException used for 011000 EBICS error
 */
class DownloadPostprocessDoneException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '011000',
            $responseMessage,
            'The positive acknowledgment of the EBICS response that is ' .
            'sent to the client from the server.'
        );
    }
}
