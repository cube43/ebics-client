<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * PartnerIdMismatchException used for 091120 EBICS error
 */
class PartnerIdMismatchException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091120',
            $responseMessage,
            'The partner ID of the electronic signature file differs from the partner ID of the submitter.'
        );
    }
}
