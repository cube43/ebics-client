<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

class EbicsResponseException extends EbicsException
{
    private string|null $request = null;

    private string|null $response = null;

    public function __construct(private string $responseCode, string|null $responseMessage, private string|null $meaning = null)
    {
        $message = $responseMessage ?: $meaning;

        parent::__construct((string) $message, (int) $responseCode);
    }

    public function getRequest(): string|null
    {
        return $this->request;
    }

    public function getResponse(): string|null
    {
        return $this->response;
    }

    public function getMeaning(): string|null
    {
        return $this->meaning;
    }

    public function getResponseCode(): string
    {
        return $this->responseCode;
    }

    public function setRequest(string $request): void
    {
        $this->request = $request;
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }
}
