<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * TxRecoverySyncException used for 061101 EBICS error
 */
class TxRecoverySyncException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '061101',
            $responseMessage,
            'If the bank supports transaction recovery, the bank verifies whether ' .
            'an upload transaction can be recovered. The server synchronizes with the client ' .
            'to recover the transaction.'
        );
    }
}
