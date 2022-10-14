<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * CertificatesValidationErrorException used for 091219 EBICS error
 */
class CertificatesValidationErrorException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091219',
            $responseMessage,
            'The server is unable to match the certificate with the ' .
            'previously declared information automatically.',
        );
    }
}
