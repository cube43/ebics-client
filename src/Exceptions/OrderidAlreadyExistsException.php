<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * OrderidAlreadyExistsException used for 091115 EBICS error
 */
class OrderidAlreadyExistsException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091115',
            $responseMessage,
            'The submitted order number already exists.'
        );
    }
}
