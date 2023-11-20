<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions\X509;

use Cube43\Component\Ebics\Exceptions\EbicsResponseException;

/**
 * X509InvalidThumbprintException used for 091212 EBICS error
 */
class X509InvalidThumbprintException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091212',
            $responseMessage,
            'The thumb print does not correspond to the certificate.',
        );
    }
}
