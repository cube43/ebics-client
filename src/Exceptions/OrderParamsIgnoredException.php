<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * OrderParamsIgnoredException used for 031001 EBICS error
 */
class OrderParamsIgnoredException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '031001',
            $responseMessage,
            'The supplied order parameters that are not supported by the bank are ignored.'
        );
    }
}
