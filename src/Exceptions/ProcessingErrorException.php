<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * ProcessingErrorException used for 091116 EBICS error
 */
class ProcessingErrorException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091116',
            $responseMessage,
            'When processing an EBICS request, other business-related errors occurred.',
        );
    }
}
