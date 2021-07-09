<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * DownloadPostprocessSkippedException used for 011001 EBICS error
 */
class DownloadPostprocessSkippedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '011001',
            $responseMessage,
            'The negative acknowledgment of the EBICS response that is ' .
            'sent to the client from the server.'
        );
    }
}
