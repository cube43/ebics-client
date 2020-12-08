<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use JsonSerializable;
use RuntimeException;

use function array_key_exists;
use function is_file;
use function Safe\file_get_contents;
use function Safe\json_decode;

/**
 * @psalm-immutable
 * @psalm-pure
 */
final class KeyRing implements JsonSerializable
{
    private ?UserCertificate $userCertificateA;
    private ?UserCertificate $userCertificateX;
    private ?UserCertificate $userCertificateE;
    private ?BankCertificate $bankCertificateX;
    private ?BankCertificate $bankCertificateE;
    private string $rsaPassword;

    public function __construct(
        string $rsaPassword,
        ?UserCertificate $userCertificateA = null,
        ?UserCertificate $userCertificateX = null,
        ?UserCertificate $userCertificateE = null,
        ?BankCertificate $bankCertificateX = null,
        ?BankCertificate $bankCertificateE = null
    ) {
        $this->rsaPassword      = $rsaPassword;
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
            $this->rsaPassword,
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
        if ($this->userCertificateE !== null || $this->userCertificateX !== null) {
            throw new RuntimeException('userCertificateE and userCertificateX already exist');
        }

        return new self(
            $this->rsaPassword,
            $this->userCertificateA,
            $userCertificateX,
            $userCertificateE,
            $this->bankCertificateX,
            $this->bankCertificateE,
        );
    }

    public function setBankCertificate(BankCertificate $bankCertificateX, BankCertificate $bankCertificateE): self
    {
        if ($this->bankCertificateE !== null || $this->bankCertificateX !== null) {
            throw new RuntimeException('bankCertificateX and bankCertificateE already exist');
        }

        return new self(
            $this->rsaPassword,
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

    public function getRsaPassword(): string
    {
        return $this->rsaPassword;
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
        $buildBankCertificate = static function (string $key) use ($data, $password): ?BankCertificate {
            if (array_key_exists($key, $data) && ! empty($data[$key])) {
                return BankCertificate::fromArray($data[$key], $password);
            }

            return null;
        };

        $buildUserCertificate = static function (string $key) use ($data, $password): ?UserCertificate {
            if (array_key_exists($key, $data) && ! empty($data[$key])) {
                return UserCertificate::fromArray($data[$key], $password);
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
     * @return (BankCertificate|UserCertificate|null)[]
     *
     * @psalm-return array{
     *  bankCertificateE: BankCertificate|null,
     *  bankCertificateX: BankCertificate|null,
     *  userCertificateA: UserCertificate|null,
     *  userCertificateE: UserCertificate|null,
     *  userCertificateX: UserCertificate|null
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'bankCertificateE' => $this->bankCertificateE,
            'bankCertificateX' => $this->bankCertificateX,
            'userCertificateA' => $this->userCertificateA,
            'userCertificateE' => $this->userCertificateE,
            'userCertificateX' => $this->userCertificateX,
        ];
    }
}
