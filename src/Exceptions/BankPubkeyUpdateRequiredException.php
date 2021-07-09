<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * BankPubkeyUpdateRequiredException used for 091008 EBICS error
 */
class BankPubkeyUpdateRequiredException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091008',
            $responseMessage,
            'The bank verifies the hash value sent by the user. ' .
            'If the hash value does not match the current public keys, ' .
            'the bank terminates the transaction initialization.'
        );
    }
}
