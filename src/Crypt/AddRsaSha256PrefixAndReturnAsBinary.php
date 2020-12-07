<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Crypt;

use function Safe\pack;
use function Safe\unpack;

/**
 * @internal
 */
class AddRsaSha256PrefixAndReturnAsBinary
{
    private const RSA_SHA256_PREFIX = [0x30, 0x31, 0x30, 0x0D, 0x06, 0x09, 0x60, 0x86, 0x48, 0x01, 0x65, 0x03, 0x04, 0x02, 0x01, 0x05, 0x00, 0x04, 0x20];

    public function __invoke(string $hash): string
    {
        return pack('c*', ...self::RSA_SHA256_PREFIX, ...unpack('C*', $hash));
    }
}
