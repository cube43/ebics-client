<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * KeymgmtUnsupportedVersionEncryptionException used for 091203 EBICS error
 */
class KeymgmtUnsupportedVersionEncryptionException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091203',
            $responseMessage,
            'When processing an HIA request, the order data contains an inadmissible ' .
            'version of the encryption process.'
        );
    }
}
