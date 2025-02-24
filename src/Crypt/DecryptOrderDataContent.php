<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\OrderDataEncrypted;
use ErrorException;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\RSA;
use RuntimeException;

use function gzuncompress;

use const OPENSSL_ZERO_PADDING;

/** @internal */
class DecryptOrderDataContent
{
    public function __invoke(KeyRing $keyRing, OrderDataEncrypted $orderData): string
    {
        $rsa = new RSA();
        $rsa->setPassword($keyRing->getPassword());
        $rsa->loadKey($keyRing->getUserCertificateE()->getPrivateKey()->value());
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);

        $transactionKeyDecrypted = $rsa->decrypt($orderData->getTransactionKey());

        // aes-128-cbc encrypting format.
        $aes = new AES(AES::MODE_CBC);
        $aes->setKeyLength(128);
        $aes->setKey($transactionKeyDecrypted);

        // Force openssl_options.
        // phpcs:ignore
        $aes->openssl_options = OPENSSL_ZERO_PADDING;

        $decrypted = $aes->decrypt($orderData->getOrderData());

        if (empty($decrypted)) {
            throw new RuntimeException('decrypt error');
        }

        return self::gzuncompress($decrypted);
    }

    private static function gzuncompress(string $string): string
    {
        $safeResult = gzuncompress($string);
        if ($safeResult === false) {
            throw new ErrorException('An error occured');
        }

        return $safeResult;
    }
}
