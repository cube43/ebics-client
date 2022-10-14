<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * OnlyX509SupportException used for 091217 EBICS error
 */
class OnlyX509SupportException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091217',
            $responseMessage,
            'The bank supports evaluation of X.509 data only.',
        );
    }
}
