<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * TxMessageReplayException used for 091103 EBICS error
 */
class TxMessageReplayException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091103',
            $responseMessage,
            'To avoid replay, the bank compares the received Nonce with the list of nonce ' .
            'values that were received previously and stored locally. If the nonce received is ' .
            'greater than the tolerance period specified by the bank, ' .
            'the response EBICS_TX_MESSAGE_REPLAY is returned.'
        );
    }
}
