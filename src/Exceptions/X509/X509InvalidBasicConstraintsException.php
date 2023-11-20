<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions\X509;

use Cube43\Component\Ebics\Exceptions\EbicsResponseException;

/**
 * X509InvalidBasicConstraintsException used for 091216 EBICS error
 */
class X509InvalidBasicConstraintsException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091216',
            $responseMessage,
            'The basic constraints are not valid when determining certificate verification.',
        );
    }
}
