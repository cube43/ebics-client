<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\OrderDataEncrypted;
use ErrorException;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey as RsaPrivateKey;
use RuntimeException;

use function gzuncompress;

/** @internal */
class DecryptOrderDataContent
{
    public function __invoke(KeyRing $keyRing, OrderDataEncrypted $orderData): string
    {
        /** @var RsaPrivateKey $rsa */
        $rsa = RSA::loadPrivateKey(
            $keyRing->getUserCertificateE()->getPrivateKey()->value(),
            $keyRing->getPassword(),
        );
        $rsa = $rsa->withPadding(RSA::ENCRYPTION_PKCS1);

        $transactionKeyDecrypted = $rsa->decrypt($orderData->getTransactionKey());

        $aes = new AES('cbc');
        $aes->setKeyLength(128);
        $aes->setKey($transactionKeyDecrypted);
        $aes->setIV(str_repeat("\0", 16));
        $aes->disablePadding();

        $decrypted = $aes->decrypt(base64_decode($orderData->getOrderData()));

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
