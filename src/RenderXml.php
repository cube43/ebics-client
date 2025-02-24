<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use ErrorException;

use function array_keys;
use function array_values;
use function file_get_contents;
use function str_replace;

/** @internal */
class RenderXml
{
    private readonly string $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/Command/xml/';
    }

    /** @param array<string, mixed> $search */
    public function __invoke(array $search, Version $version, string $file): DOMDocument
    {
        return new DOMDocument(str_replace(array_keys($search), array_values($search), self::fileGetContents($this->filePath . $version->value() . '/' . $file)));
    }

    /** @param array<string, mixed> $search */
    public function renderXmlRaw(array $search, Version $version, string $file): string
    {
        return str_replace(array_keys($search), array_values($search), self::fileGetContents($this->filePath . $version->value() . '/' . $file));
    }

    private static function fileGetContents(string $string): string
    {
        $safeResult = file_get_contents($string);
        if ($safeResult === false) {
            throw new ErrorException('An error occured');
        }

        return $safeResult;
    }
}
