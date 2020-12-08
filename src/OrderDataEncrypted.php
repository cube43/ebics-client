<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

/**
 * @internal
 *
 * @psalm-immutable
 */
class OrderDataEncrypted
{
    private string $orderData;
    private string $transactionKey;

    public function __construct(string $orderData, string $transactionKey)
    {
        $this->orderData      = $orderData;
        $this->transactionKey = $transactionKey;
    }

    public function getOrderData(): string
    {
        return $this->orderData;
    }

    public function getTransactionKey(): string
    {
        return $this->transactionKey;
    }
}
