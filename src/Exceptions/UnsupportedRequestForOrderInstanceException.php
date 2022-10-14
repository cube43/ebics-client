<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * UnsupportedRequestForOrderInstanceException used for 090006 EBICS error
 */
class UnsupportedRequestForOrderInstanceException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '090006',
            $responseMessage,
            'In the case of some business transactions, it is not possible to ' .
            'retrieve detailed information of the order data.',
        );
    }
}
