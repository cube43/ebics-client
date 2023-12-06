<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\E2e\Command;

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\Command\HIACommand;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\SymfonyEbicsServerCaller;
use Cube43\Component\Ebics\UserCertificate;
use Cube43\Component\Ebics\Version;
use Cube43\Component\Ebics\X509\DefaultX509OptionGenerator;
use Symfony\Component\HttpClient\MockHttpClient;

class HIACommandTest extends E2eTestBase
{
    /** @return iterable<string, array<int, Version>> */
    public static function provideVersion(): iterable
    {
        yield 'v24' => [Version::v24()];
        yield 'v25' => [Version::v25()];
        //yield [Version::v30()];
    }

    /** @dataProvider provideVersion */
    public function testOk(Version $version): void
    {
        $versionToXmlResponse = [
            Version::v24()->value() => '<?xml version="1.0" encoding="UTF-8" standalone="no"?><ebicsKeyManagementResponse xmlns="http://www.ebics.org/H003" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H003" xsi:schemaLocation="http://www.ebics.org/H003 http://www.ebics.org/H003/ebics_keymgmt_response.xsd"><header authenticate="true"><static/><mutable><ReturnCode>000000</ReturnCode><ReportText>[EBICS_OK] OK</ReportText></mutable></header><body><ReturnCode authenticate="true">000000</ReturnCode></body></ebicsKeyManagementResponse>',
            Version::v25()->value() => '<?xml version="1.0" encoding="UTF-8" standalone="no"?><ebicsKeyManagementResponse xmlns="urn:org:ebics:H004" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Revision="1" Version="H004" xsi:schemaLocation="urn:org:ebics:H004 ebics_keymgmt_response_H004.xsd"><header authenticate="true"><static/><mutable><OrderID>A07E</OrderID><ReturnCode>000000</ReturnCode><ReportText>[EBICS_OK] OK</ReportText></mutable></header><body><ReturnCode authenticate="true">000000</ReturnCode></body></ebicsKeyManagementResponse>',
        ];

        $sUT = new HIACommand(
            new SymfonyEbicsServerCaller(new MockHttpClient($this->getCallback($versionToXmlResponse[$version->value()], $version, false))),
        );

        $bank    = new BankInfo('myHostId', 'http://myurl.com', $version, 'myPartId', 'myUserId');
        $keyRing = new KeyRing('');

        $keyring = $sUT->__invoke($bank, $keyRing, new DefaultX509OptionGenerator());

        self::assertInstanceOf(UserCertificate::class, $keyring->getUserCertificateX());
        self::assertInstanceOf(UserCertificate::class, $keyring->getUserCertificateE());
    }
}
