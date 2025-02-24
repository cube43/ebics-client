<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use ErrorException;

use function pack;
use function unpack;

/** @internal */
class AddRsaSha256PrefixAndReturnAsBinary
{
    private const RSA_SHA256_PREFIX = [0x30, 0x31, 0x30, 0x0D, 0x06, 0x09, 0x60, 0x86, 0x48, 0x01, 0x65, 0x03, 0x04, 0x02, 0x01, 0x05, 0x00, 0x04, 0x20];

    public function __invoke(string $hash): string
    {
        return self::pack('c*', ...self::RSA_SHA256_PREFIX, ...self::unpack('C*', $hash));
    }

    private static function unpack(string $format, string $string): array
    {
        $safeResult = unpack($format, $string);
        if ($safeResult === false) {
            throw new ErrorException('An error occured');
        }

        return $safeResult;
    }

    private static function pack(string $string, mixed ...$values): string
    {
        $safeResult = pack($string, ...$values);
        if ($safeResult === false) {
            throw new ErrorException('An error occured');
        }

        return $safeResult;
    }
}
