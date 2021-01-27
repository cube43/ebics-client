<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\RenderXml;
use Cube43\Component\Ebics\Version;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass RenderXml
 */
class RenderXmlTest extends TestCase
{
    public function testInvoke(): void
    {
        $sUT = new RenderXml();

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?>
<ebicsUnsecuredRequest xmlns="http://www.ebics.org/H003" Revision="1" Version="H003">
    <header authenticate="true">
        <static>
            <HostID>b</HostID>
            <PartnerID>{{PartnerID}}</PartnerID>
            <UserID>{{UserID}}</UserID>
            <Product Language="fr">Cube43 Ebics client PHP</Product>
            <OrderDetails>
                <OrderType>INI</OrderType>
                <OrderID>A102</OrderID>
                <OrderAttribute>DZNNN</OrderAttribute>
            </OrderDetails>
            <SecurityMedium>0000</SecurityMedium>
        </static>
        <mutable/>
    </header>
    <body>
        <DataTransfer>
            <OrderData>{{OrderData}}</OrderData>
        </DataTransfer>
    </body>
</ebicsUnsecuredRequest>', $sUT->__invoke(['{{HostID}}' => 'b', '{{OrderID}}' => 'A102'], Version::v24(), 'INI.xml')->toString());
    }

    public function testRawXml(): void
    {
        $sUT = new RenderXml();

        self::assertXmlStringEqualsXmlString('<?xml version="1.0"?>
<ebicsUnsecuredRequest xmlns="http://www.ebics.org/H003" Revision="1" Version="H003">
    <header authenticate="true">
        <static>
            <HostID>b</HostID>
            <PartnerID>{{PartnerID}}</PartnerID>
            <UserID>{{UserID}}</UserID>
            <Product Language="fr">Cube43 Ebics client PHP</Product>
            <OrderDetails>
                <OrderType>INI</OrderType>
                <OrderID>A102</OrderID>
                <OrderAttribute>DZNNN</OrderAttribute>
            </OrderDetails>
            <SecurityMedium>0000</SecurityMedium>
        </static>
        <mutable/>
    </header>
    <body>
        <DataTransfer>
            <OrderData>{{OrderData}}</OrderData>
        </DataTransfer>
    </body>
</ebicsUnsecuredRequest>', $sUT->renderXmlRaw(['{{HostID}}' => 'b', '{{OrderID}}' => 'A102'], Version::v24(), 'INI.xml'));
    }
}
