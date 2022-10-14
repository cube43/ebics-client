<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * TxAbortException used for 091102 EBICS error
 */
class TxAbortException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091102',
            $responseMessage,
            'If the bank supports transaction recovery, the bank verifies whether ' .
            'an upload transaction can be recovered. If the transaction cannot be recovered, ' .
            'the bank terminates the transaction.',
        );
    }
}
