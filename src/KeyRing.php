<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

use JsonSerializable;
use RuntimeException;

use function array_key_exists;
use function is_file;
use function Safe\file_get_contents;
use function Safe\json_decode;

class KeyRing implements JsonSerializable
{
    private ?UserCertificate $userCertificateA;
    private ?UserCertificate $userCertificateX;
    private ?UserCertificate $userCertificateE;
    private ?BankCertificate $bankCertificateX;
    private ?BankCertificate $bankCertificateE;
    private string $password;

    public function __construct(
        string $password,
        ?UserCertificate $userCertificateA = null,
        ?UserCertificate $userCertificateX = null,
        ?UserCertificate $userCertificateE = null,
        ?BankCertificate $bankCertificateX = null,
        ?BankCertificate $bankCertificateE = null
    ) {
        $this->password         = $password;
        $this->userCertificateA = $userCertificateA;
        $this->userCertificateX = $userCertificateX;
        $this->userCertificateE = $userCertificateE;
        $this->bankCertificateX = $bankCertificateX;
        $this->bankCertificateE = $bankCertificateE;
    }

    public function setUserCertificateA(UserCertificate $certificate): self
    {
        if ($this->userCertificateA !== null) {
            throw new RuntimeException('userCertificateA already exist');
        }

        return new self(
            $this->password,
            $certificate,
            $this->userCertificateX,
            $this->userCertificateE,
            $this->bankCertificateX,
            $this->bankCertificateE,
        );
    }

    public function hasUserCertificatA(): bool
    {
        return $this->userCertificateA !== null;
    }

    public function hasUserCertificateEAndX(): bool
    {
        return $this->userCertificateE !== null && $this->userCertificateX !== null;
    }

    public function hasBankCertificate(): bool
    {
        return $this->bankCertificateX !== null && $this->bankCertificateE !== null;
    }

    public function setUserCertificateEAndX(UserCertificate $userCertificateE, UserCertificate $userCertificateX): self
    {
        if ($this->userCertificateE !== null) {
            throw new RuntimeException('userCertificateE already exist');
        }

        if ($this->userCertificateX !== null) {
            throw new RuntimeException('userCertificateX already exist');
        }

        return new self(
            $this->password,
            $this->userCertificateA,
            $userCertificateX,
            $userCertificateE,
            $this->bankCertificateX,
            $this->bankCertificateE,
        );
    }

    public function setBankCertificate(BankCertificate $bankCertificateX, BankCertificate $bankCertificateE): self
    {
        if ($this->bankCertificateX !== null) {
            throw new RuntimeException('bankCertificateX already exist');
        }

        if ($this->bankCertificateE !== null) {
            throw new RuntimeException('bankCertificateE already exist');
        }

        return new self(
            $this->password,
            $this->userCertificateA,
            $this->userCertificateX,
            $this->userCertificateE,
            $bankCertificateX,
            $bankCertificateE,
        );
    }

    public function getUserCertificateA(): UserCertificate
    {
        if ($this->userCertificateA === null) {
            throw new RuntimeException('userCertificateA empty');
        }

        return $this->userCertificateA;
    }

    public function getUserCertificateX(): UserCertificate
    {
        if ($this->userCertificateX === null) {
            throw new RuntimeException('userCertificateX empty');
        }

        return $this->userCertificateX;
    }

    public function getUserCertificateE(): UserCertificate
    {
        if ($this->userCertificateE === null) {
            throw new RuntimeException('userCertificateE empty');
        }

        return $this->userCertificateE;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getBankCertificateX(): BankCertificate
    {
        if ($this->bankCertificateX === null) {
            throw new RuntimeException('bankCertificateX empty');
        }

        return $this->bankCertificateX;
    }

    public function getBankCertificateE(): BankCertificate
    {
        if ($this->bankCertificateE === null) {
            throw new RuntimeException('bankCertificateE empty');
        }

        return $this->bankCertificateE;
    }

    public static function fromFile(string $file, string $password): self
    {
        if (! is_file($file)) {
            return new self($password);
        }

        return self::fromArray(json_decode(file_get_contents($file), true), $password);
    }

    /**
     * @param array<string, (array<string, string>|null)> $data
     */
    public static function fromArray(array $data, string $password): self
    {
        $buildBankCertificate = static function (string $key) use ($data): ?BankCertificate {
            if (array_key_exists($key, $data) && ! empty($data[$key])) {
                return BankCertificate::fromArray($data[$key]);
            }

            return null;
        };

        $buildUserCertificate = static function (string $key) use ($data): ?UserCertificate {
            if (array_key_exists($key, $data) && ! empty($data[$key])) {
                return UserCertificate::fromArray($data[$key]);
            }

            return null;
        };

        return new self(
            $password,
            $buildUserCertificate('userCertificateA'),
            $buildUserCertificate('userCertificateX'),
            $buildUserCertificate('userCertificateE'),
            $buildBankCertificate('bankCertificateX'),
            $buildBankCertificate('bankCertificateE'),
        );
    }

    /**
     * @return array<string, (array<string, string>|null)>
     */
    public function jsonSerialize(): array
    {
        return [
            'bankCertificateE' => $this->bankCertificateE ? $this->bankCertificateE->jsonSerialize() : null,
            'bankCertificateX' => $this->bankCertificateX ? $this->bankCertificateX->jsonSerialize() : null,
            'userCertificateA' => $this->userCertificateA ? $this->userCertificateA->jsonSerialize() : null,
            'userCertificateE' => $this->userCertificateE ? $this->userCertificateE->jsonSerialize() : null,
            'userCertificateX' => $this->userCertificateX ? $this->userCertificateX->jsonSerialize() : null,
        ];
    }
}
