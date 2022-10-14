<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * TxUnknownTxidException used for 091101 EBICS error
 */
class TxUnknownTxidException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091101',
            $responseMessage,
            'The supplied transaction ID is invalid.',
        );
    }
}
