<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * X509InvalidPolicyException used for 091215 EBICS error
 */
class X509InvalidPolicyException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091215',
            $responseMessage,
            'The certificate has invalid policy when determining certificate verification.'
        );
    }
}
