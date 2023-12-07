<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\X509;

enum EbicsX509FormatEnum: string
{
    case PEM = 'PEM';
    case DER = 'DER';
}
