<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidXmlException used for 091010 EBICS error
 */
class InvalidXmlException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091010',
            $responseMessage,
            'The XML schema does not conform to the EBICS specifications.',
        );
    }
}
