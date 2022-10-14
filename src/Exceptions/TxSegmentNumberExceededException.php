<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * TxSegmentNumberExceededException used for 091104 EBICS error
 */
class TxSegmentNumberExceededException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091104',
            $responseMessage,
            'The serial number of the transmitted order data segment must be ' .
            'less than or equal to the total number of data segments that are to be transmitted. ' .
            'The transaction is terminated if the number of transmitted order ' .
            'data segments exceeds the total number of data segments.',
        );
    }
}
