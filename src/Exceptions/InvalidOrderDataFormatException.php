<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidOrderDataFormatException used for 090004 EBICS error
 */
class InvalidOrderDataFormatException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '090004',
            $responseMessage,
            'The order data does not correspond with the designated format.'
        );
    }
}
