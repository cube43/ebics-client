<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * MaxTransactionsExceededException used for 091119 EBICS error
 */
class MaxTransactionsExceededException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091119',
            $responseMessage,
            'The maximum number of parallel transactions per customer is exceeded.',
        );
    }
}
