<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * InvalidUserOrUserStateException used for 091002 EBICS error
 */
class InvalidUserOrUserStateException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091002',
            $responseMessage,
            'Error that results from an invalid combination of user ID or an invalid subscriber state.',
        );
    }
}
