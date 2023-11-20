<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions\X509;

use Cube43\Component\Ebics\Exceptions\EbicsResponseException;

/**
 * X509CertificateNotValidYetException used for 091209 EBICS error
 */
class X509CertificateNotValidYetException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091209',
            $responseMessage,
            'The certificate is not valid because it is not yet in effect.',
        );
    }
}
