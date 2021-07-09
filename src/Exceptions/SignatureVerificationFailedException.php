<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * SignatureVerificationFailedException used for 091301 EBICS error
 */
class SignatureVerificationFailedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091301',
            $responseMessage,
            'Verification of the electronic signature has failed.'
        );
    }
}
