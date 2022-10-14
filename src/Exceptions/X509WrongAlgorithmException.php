<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * X509WrongAlgorithmException used for 091211 EBICS error
 */
class X509WrongAlgorithmException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091211',
            $responseMessage,
            'When verifying the certificate algorithm, the bank ' .
            'detects that the certificate is not issued for current use.',
        );
    }
}
