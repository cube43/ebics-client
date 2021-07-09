<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * OrderidUnknownException used for 091114 EBICS error
 */
class OrderidUnknownException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091114',
            $responseMessage,
            'Upon verification, the bank finds that the order is not located in the VEU processing system.'
        );
    }
}
