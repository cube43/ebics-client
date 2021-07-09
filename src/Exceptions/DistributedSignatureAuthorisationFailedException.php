<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * DistributedSignatureAuthorisationFailedException used for 091007 EBICS error
 */
class DistributedSignatureAuthorisationFailedException extends EbicsResponseException
{
    public function __construct(?string $responseMessage = null)
    {
        parent::__construct(
            '091007',
            $responseMessage,
            'Subscriber possesses no authorization of signature for ' .
            'the referenced order in the VEU administration.'
        );
    }
}
