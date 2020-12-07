<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

use function array_keys;
use function array_values;
use function Safe\file_get_contents;
use function str_replace;

/**
 * @internal
 */
class RenderXml
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/Command/xml/';
    }

    /**
     * @param array<string, mixed> $search
     */
    public function __invoke(array $search, Version $version, string $file): DOMDocument
    {
        return new DOMDocument(str_replace(array_keys($search), array_values($search), file_get_contents($this->filePath . $version->value() . '/' . $file)));
    }

    /**
     * @param array<string, mixed> $search
     */
    public function renderXmlRaw(array $search, Version $version, string $file): string
    {
        return str_replace(array_keys($search), array_values($search), file_get_contents($this->filePath . $version->value() . '/' . $file));
    }
}
