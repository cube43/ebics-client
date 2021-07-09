<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * SegmentSizeExceededException used for 091009 EBICS error
 */
class SegmentSizeExceededException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091009',
            $responseMessage,
            'If the size of the transmitted order data segment exceeds 1 MB, the transaction is terminated.'
        );
    }
}
