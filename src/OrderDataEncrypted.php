<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

/**
 * @internal
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
