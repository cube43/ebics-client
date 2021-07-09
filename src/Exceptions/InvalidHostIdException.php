<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidHostIdException used for 091011 EBICS error
 */
class InvalidHostIdException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091011',
            $responseMessage,
            'The transmitted host ID is not known to the bank.'
        );
    }
}
