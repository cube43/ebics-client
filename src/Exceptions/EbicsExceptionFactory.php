<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

use function assert;

/**
 * Exception factory with an EBICS response code
 */
class EbicsExceptionFactory
{
    /** @throws EbicsResponseException */
    public static function buildExceptionFromCode(
        string $errorCode,
        string|null $errorText = null,
        string|null $request = null,
        string|null $response = null,
    ): void {
        if (! empty(EbicsErrorCodeMapping::$mapping[$errorCode])) {
            $exceptionClass = EbicsErrorCodeMapping::$mapping[$errorCode];

            $exception = new $exceptionClass($errorText);
            assert($exception instanceof EbicsResponseException);
        } else {
            $exception = new EbicsResponseException($errorCode, $errorText);
        }

        if ($request !== null) {
            $exception->setRequest($request);
        }

        if ($response !== null) {
            $exception->setResponse($response);
        }

        throw $exception;
    }
}
