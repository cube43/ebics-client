<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * MaxOrderDataSizeExceededException used for 091117 EBICS error
 */
class MaxOrderDataSizeExceededException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091117',
            $responseMessage,
            'The bank does not support the requested order size.',
        );
    }
}
