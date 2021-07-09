<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * KeymgmtDuplicateKeyException used for 091218 EBICS error
 */
class KeymgmtDuplicateKeyException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091218',
            $responseMessage,
            'The key sent for authentication or encryption is the same as the signature key.'
        );
    }
}
