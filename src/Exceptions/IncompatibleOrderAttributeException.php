<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

/**
 * IncompatibleOrderAttributeException used for 091121 EBICS error
 */
class IncompatibleOrderAttributeException extends EbicsResponseException
{
    public function __construct(string|null $responseMessage = null)
    {
        parent::__construct(
            '091121',
            $responseMessage,
            'The specified order attribute is not compatible with the order in the bank system. ' .
            'If the bank has a file with the attribute DZHNN or other electronic signature files ' .
            '(for example, with the attribute UZHNN) for the same order, then the use of the order ' .
            'attributes DZHNN is not allowed. Also, if the bank already has the same order and the ' .
            'order was transmitted with the order attributes DZHNN, then again the use of the order ' .
            'attributes DZHNN is not allowed.',
        );
    }
}
