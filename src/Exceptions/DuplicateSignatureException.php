<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * DuplicateSignatureException used for 091306 EBICS error
 */
class DuplicateSignatureException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091306',
            $responseMessage,
            'The signatory has already signed the order.',
        );
    }
}
