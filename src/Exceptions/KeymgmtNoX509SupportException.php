<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * KeymgmtNoX509SupportException used for 091207 EBICS error
 */
class KeymgmtNoX509SupportException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091207',
            $responseMessage,
            'A public key of type X509 is sent to the bank but the bank ' .
            'supports only public key value type.',
        );
    }
}
