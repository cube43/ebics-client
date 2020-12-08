<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

interface Key
{
    public function value(): string;

    public function password(): string;
}
