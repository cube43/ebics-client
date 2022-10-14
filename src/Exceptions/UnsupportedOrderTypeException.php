<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * UnsupportedOrderTypeException used for 091006 EBICS error
 */
class UnsupportedOrderTypeException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091006',
            $responseMessage,
            'Upon verification, the bank finds that the order type ' .
            'specified in valid but not supported by the bank.',
        );
    }
}
