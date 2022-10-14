<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * AmountCheckFailedException used for 091303 EBICS error
 */
class AmountCheckFailedException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091303',
            $responseMessage,
            'Preliminary verification of the account amount limit has failed.',
        );
    }
}
