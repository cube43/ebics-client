<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\Key;
use Cube43\Component\Ebics\OrderDataEncrypted;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\RSA;
use RuntimeException;

use function Safe\gzuncompress;

use const OPENSSL_ZERO_PADDING;

/**
 * @internal
 *
 * @psalm-pure
 */
final class DecryptOrderDataContent
{
    public function __invoke(Key $key, OrderDataEncrypted $orderData): string
    {
        return $this->aesDecrypt($this->rsaDecrypt($key, $orderData), $orderData);
    }

    private function rsaDecrypt(Key $key, OrderDataEncrypted $orderData): string
    {
        $rsa = new RSA();
        $rsa->setPassword($key->password());
        $rsa->loadKey($key->value());
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);

        $transactionKeyDecrypted = @$rsa->decrypt($orderData->getTransactionKey());

        if (empty($transactionKeyDecrypted)) {
            throw new RuntimeException('decrypt error');
        }

        return $transactionKeyDecrypted;
    }

    private function aesDecrypt(string $transactionKeyDecrypted, OrderDataEncrypted $orderData): string
    {
        // aes-128-cbc encrypting format.
        $aes = new AES(AES::MODE_CBC);
        $aes->setKeyLength(128);
        $aes->setKey($transactionKeyDecrypted);

        // Force openssl_options.
        // phpcs:ignore
        $aes->openssl_options = OPENSSL_ZERO_PADDING;

        $decrypted = @$aes->decrypt($orderData->getOrderData());

        if (empty($decrypted)) {
            throw new RuntimeException('decrypt error');
        }

        return gzuncompress($decrypted);
    }
}
