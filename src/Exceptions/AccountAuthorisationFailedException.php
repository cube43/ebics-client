<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * AccountAuthorisationFailedException used for 091302 EBICS error
 */
class AccountAuthorisationFailedException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091302',
            $responseMessage,
            'Preliminary verification of the account authorization has failed.',
        );
    }
}
