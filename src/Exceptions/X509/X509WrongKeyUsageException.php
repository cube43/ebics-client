<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions\X509;

use Cube43\Component\Ebics\Exceptions\EbicsResponseException;

/**
 * X509WrongKeyUsageException used for 091210 EBICS error
 */
class X509WrongKeyUsageException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091210',
            $responseMessage,
            'When verifying the certificate key usage, the bank ' .
            'detects that the certificate is not issued for current use.',
        );
    }
}
