<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use DOMNode;
use RuntimeException;
use Throwable;

use function error_get_last;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;

/** @internal */
class DOMDocument
{
    private readonly \DOMDocument $document;

    public function __construct(string $content)
    {
        if (empty($content)) {
            throw new RuntimeException('content is empty');
        }

        $document = new \DOMDocument('1.0', 'utf-8');
        try {
            $return = @$document->loadXML($content);

            if ($return !== true) {
                throw new RuntimeException(error_get_last()['message'] ?? 'An error occured');
            }
        } catch (Throwable $exception) {
            throw new RuntimeException($content, 0, $exception);
        }

        $this->document = $document;
    }

    public function getNodeValueChildOf(string $node, string $parent): string
    {
        $parentNode = $this->document->getElementsByTagName($parent)->item(0);

        if ($parentNode === null) {
            throw new RuntimeException('node parent ' . $parent . ' not found');
        }

        $return = $this->recrusiveChildNode($parentNode, $node);

        if ($return !== null) {
            return $return;
        }

        throw new RuntimeException('node "' . $node . '" not found in parent ' . $parent);
    }

    private function recrusiveChildNode(DOMNode $parentNode, string $node): string|null
    {
        foreach ($parentNode->childNodes as $element) {
            $nodeName = $element->nodeName;

            if (strpos($nodeName, ':')) {
                $nodeName = substr($nodeName, (int) strpos($nodeName, ':') + 1, strlen($nodeName));
            }

            if ($nodeName === $node) {
                return $element->nodeValue;
            }

            if (! $element->hasChildNodes()) {
                continue;
            }

            $return = $this->recrusiveChildNode($element, $node);

            if ($return !== null) {
                return $return;
            }
        }

        return null;
    }

    public function getNodeValue(string $nodeName, int $index = 0): string
    {
        $node = $this->document->getElementsByTagName($nodeName)->item($index);

        if ($node === null) {
            throw new RuntimeException(sprintf('node "%s" not found in %s', $nodeName, $this->toString()));
        }

        return $node->nodeValue ?? '';
    }

    public function toString(): string
    {
        $this->document->preserveWhiteSpace = false;
        $this->document->formatOutput       = false;

        $content = (string) $this->document->saveXML();
        $content = str_replace('<?xml version="1.0" encoding="utf-8"?>', "<?xml version='1.0' encoding='utf-8'?>", $content);

        return trim($content);
    }

    /**
     * Get formatted content.
     */
    public function getFormattedContent(): string
    {
        $this->document->formatOutput = true;

        return (string) $this->document->saveXML();
    }
}
