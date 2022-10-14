<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidSignerStateException used for 091305 EBICS error
 */
class InvalidSignerStateException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091305',
            $responseMessage,
            'The state of the signatory is not admissible.',
        );
    }
}
