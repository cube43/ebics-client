<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Exceptions;

class EbicsResponseException extends EbicsException
{
    private string $responseCode;

    private ?string $request = null;

    private ?string $response = null;

    private ?string $meaning = null;

    public function __construct(string $responseCode, ?string $responseMessage, ?string $meaning = null)
    {
        $message = $responseMessage ?: $meaning;

        parent::__construct((string) $message, (int) $responseCode);

        $this->responseCode = $responseCode;
        $this->meaning      = $meaning;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function getMeaning(): ?string
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
