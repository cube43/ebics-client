<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidOrderTypeException used for 091005 EBICS error
 */
class InvalidOrderTypeException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091005',
            $responseMessage,
            'Upon verification, the bank finds that the order type specified in invalid.'
        );
    }
}
