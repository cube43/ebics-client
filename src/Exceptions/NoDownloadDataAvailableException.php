<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * NoDownloadDataAvailableException used for 090005 EBICS error
 */
class NoDownloadDataAvailableException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '090005',
            $responseMessage,
            'If the requested download data is not available, the EBICS transaction is terminated.',
        );
    }
}
