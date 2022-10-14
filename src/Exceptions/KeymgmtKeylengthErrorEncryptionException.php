<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * KeymgmtKeylengthErrorEncryptionException used for 091206 EBICS error
 */
class KeymgmtKeylengthErrorEncryptionException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091206',
            $responseMessage,
            'When processing an HIA request, the order data contains an ' .
            'encryption key of inadmissible length.',
        );
    }
}
