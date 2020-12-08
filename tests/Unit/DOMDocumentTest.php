<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\DOMDocument;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass DOMDocument
 */
class DOMDocumentTest extends TestCase
{
    public function testGetNodeValueOk(): void
    {
        $sUT = new DOMDocument('<?xml version="1.0" encoding="utf-8"?><test>hello</test>');

        self::assertXmlStringEqualsXmlString("<?xml version='1.0' encoding='utf-8'?><test>hello</test>", $sUT->getFormattedContent());
        self::assertSame('hello', $sUT->getNodeValue('test'));
    }

    public function testGetNodeValueFail(): void
    {
        $sUT = new DOMDocument('<?xml version="1.0" encoding="utf-8"?><test>hello</test>');

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('node "toto" not found');
        $sUT->getNodeValue('toto');
    }

    public function testGetNodeValueChildOfOk(): void
    {
        $sUT = new DOMDocument('<?xml version="1.0" encoding="utf-8"?><test><toto>héhé</toto></test>');

        self::assertSame('héhé', $sUT->getNodeValueChildOf('toto', 'test'));
    }

    public function testGetNodeValueChildOfOkWithNs(): void
    {
        $sUT = new DOMDocument('<?xml version="1.0" encoding="utf-8"?><test><test:toto>héhé</test:toto></test>');

        self::assertSame('héhé', $sUT->getNodeValueChildOf('toto', 'test'));
    }

    public function testGetNodeValueChildOfParentNotFound(): void
    {
        $sUT = new DOMDocument('<?xml version="1.0" encoding="utf-8"?><test><toto>héhé</toto></test>');

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('node parent undefined not found');
        $sUT->getNodeValueChildOf('toto', 'undefined');
    }

    public function testGetNodeValueChildOfChildNotFound(): void
    {
        $sUT = new DOMDocument('<?xml version="1.0" encoding="utf-8"?><tata><test><toto>héhé</toto></test></tata>');

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('node "undefined" not found in parent test');
        $sUT->getNodeValueChildOf('undefined', 'test');
    }
}
