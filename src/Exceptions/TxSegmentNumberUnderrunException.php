<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * TxSegmentNumberUnderrunException used for 011101 EBICS error
 */
class TxSegmentNumberUnderrunException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '011101',
            $responseMessage,
            'The server terminates the transaction if the client, in an upload transaction, ' .
            'has specified a very high (when compared to the number specified in the initialization phase) ' .
            'number of segments that are to be transmitted to the server.',
        );
    }
}
