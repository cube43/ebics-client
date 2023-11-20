<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions\X509;

use Cube43\Component\Ebics\Exceptions\EbicsResponseException;

/**
 * X509CertificateExpiredException used for 091208 EBICS error
 */
class X509CertificateExpiredException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091208',
            $responseMessage,
            'The certificate is not valid because it has expired.',
        );
    }
}
