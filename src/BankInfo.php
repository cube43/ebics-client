<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

class BankInfo
{
    private string $hostId;
    private string $url;
    private Version $version;
    private string $partnerId;
    private string $userId;

    public function __construct(string $hostId, string $url, Version $version, string $partnerId, string $userId)
    {
        $this->hostId    = $hostId;
        $this->url       = $url;
        $this->version   = $version;
        $this->partnerId = $partnerId;
        $this->userId    = $userId;
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
}
