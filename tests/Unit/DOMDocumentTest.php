<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\Unit;

use Fezfez\Ebics\DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass DOMDocument
 */
class DOMDocumentTest extends TestCase
{
    public function testGetter(): void
    {
        $sUT = new DOMDocument('<?xml version="1.0" encoding="utf-8"?><test>hello</test>');

        self::assertXmlStringEqualsXmlString("<?xml version='1.0' encoding='utf-8'?><test>hello</test>", $sUT->getFormattedContent());
        self::assertSame('hello', $sUT->getNodeValue('test'));
    }
}
