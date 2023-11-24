<?php

declare(strict_types=1);

use Cube43\Component\Ebics\Crypt\SignQuery;
use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\KeyRing;

$sign = new SignQuery();

$result = $sign->__invoke(
    new DOMDocument('FDL_acknowledgement.xml'),
    new KeyRing('password'),
);

var_dump($result->toString());