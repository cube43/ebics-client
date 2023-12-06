<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

class BankInfo
{
    public function __construct(
        private readonly string $hostId,
        private readonly string $url,
        private readonly Version $version,
        private readonly string $partnerId,
        private readonly string $userId,
        private readonly string|null $externalId = null,
    ) {
    }

    public function getHostId(): string
    {
        return $this->hostId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isCertified(): bool
    {
        return true;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getPartnerId(): string
    {
        return $this->partnerId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getExternalId(): string|null
    {
        return $this->externalId;
    }
}
