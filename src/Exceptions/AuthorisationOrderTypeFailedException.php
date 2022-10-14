<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * AuthorisationOrderTypeFailedException used for 090003 EBICS error
 */
class AuthorisationOrderTypeFailedException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '090003',
            $responseMessage,
            'The subscriber is not entitled to submit orders of the selected order type. ' .
            'If the authorization is missing when the bank verifies whether the subscriber has ' .
            'a bank-technical authorization of signature for the order, the transaction is cancelled.',
        );
    }
}
