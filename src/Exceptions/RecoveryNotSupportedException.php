<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * RecoveryNotSupportedException used for 091105 EBICS error
 */
class RecoveryNotSupportedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091105',
            $responseMessage,
            'If the bank does not support transaction recovery, ' .
            'the upload transaction is terminated.'
        );
    }
}
