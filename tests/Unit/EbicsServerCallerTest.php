<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Tests\Unit;

use Fezfez\Ebics\BankInfo;
use Fezfez\Ebics\EbicsServerCaller;
use Fezfez\Ebics\RequestMaker;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

/**
 * @coversDefaultClass RequestMaker
 */
class EbicsServerCallerTest extends TestCase
{
    public function testOk(): void
    {
        $reponse    = self::createMock(ResponseInterface::class);
        $httpClient = self::createMock(HttpClientInterface::class);

        $reponse->expects(self::once())->method('getContent')->willReturn('<?xml version="1.0" encoding="UTF-8"?><ReturnCode>000000</ReturnCode>');
        $httpClient->expects(self::once())->method('request')->with('POST', 'url', [
            'headers' => ['Content-Type' => 'text/xml; charset=ISO-8859-1'],
            'body' => 'test',
            'verify_peer' => false,
            'verify_host' => false,
        ])->willReturn($reponse);

        $sUT = new EbicsServerCaller($httpClient);

        $bank = self::createMock(BankInfo::class);

        $bank->expects(self::once())->method('getUrl')->willReturn('url');

        self::assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="UTF-8"?><ReturnCode>000000</ReturnCode>', $sUT->__invoke('test', $bank));
    }

    public function testFail(): void
    {
        $reponse    = self::createMock(ResponseInterface::class);
        $httpClient = self::createMock(HttpClientInterface::class);

        $reponse->expects(self::once())->method('getContent')->willReturn('<?xml version="1.0" encoding="UTF-8"?><test>');
        $httpClient->expects(self::once())->method('request')->with('POST', 'url', [
            'headers' => ['Content-Type' => 'text/xml; charset=ISO-8859-1'],
            'body' => 'test',
            'verify_peer' => false,
            'verify_host' => false,
        ])->willReturn($reponse);

        $sUT = new EbicsServerCaller($httpClient);

        $bank = self::createMock(BankInfo::class);

        $bank->expects(self::once())->method('getUrl')->willReturn('url');

        self::expectException(Throwable::class);
        $sUT->__invoke('test', $bank);
    }
}
