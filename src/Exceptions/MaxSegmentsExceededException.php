<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * MaxSegmentsExceededException used for 091118 EBICS error
 */
class MaxSegmentsExceededException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091118',
            $responseMessage,
            'The submitted number of segments for upload is very high.'
        );
    }
}
