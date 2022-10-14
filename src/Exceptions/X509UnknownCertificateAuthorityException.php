<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * X509UnknownCertificateAuthorityException used for 091214 EBICS error
 */
class X509UnknownCertificateAuthorityException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091214',
            $responseMessage,
            'The chain cannot be verified because of an unknown certificate authority (CA).',
        );
    }
}
