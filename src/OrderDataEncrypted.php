<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

/** @internal */
class OrderDataEncrypted
{
    public function __construct(
        private readonly string $orderData,
        private readonly string $transactionKey,
    ) {
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
