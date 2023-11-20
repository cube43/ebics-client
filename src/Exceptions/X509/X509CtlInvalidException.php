<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions\X509;

use Cube43\Component\Ebics\Exceptions\EbicsResponseException;

/**
 * X509CtlInvalidException used for 091213 EBICS error
 */
class X509CtlInvalidException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091213',
            $responseMessage,
            'When verifying the certificate, the bank detects ' .
            'that the certificate trust list (CTL) is not valid.',
        );
    }
}
