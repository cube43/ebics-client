<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * SignerUnknownException used for 091304 EBICS error
 */
class SignerUnknownException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091304',
            $responseMessage,
            'The signatory of the order is not a valid subscriber.'
        );
    }
}
