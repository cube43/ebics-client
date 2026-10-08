<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Tests\Unit;

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\Exceptions\InvalidOrderDataFormatException;
use Cube43\Component\Ebics\Exceptions\InvalidUserOrUserStateException;
use Cube43\Component\Ebics\RequestMaker;
use Cube43\Component\Ebics\SymfonyEbicsServerCaller;
use Cube43\Component\Ebics\Version;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

/** @coversDefaultClass RequestMaker */
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

        $sUT = new SymfonyEbicsServerCaller($httpClient);

        $bank = self::createMock(BankInfo::class);

        $bank->expects(self::once())->method('getUrl')->willReturn('url');

        self::assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="UTF-8"?><ReturnCode>000000</ReturnCode>', $sUT->__invoke('test', $bank));
    }

    public function testKeyManagementResponseAcceptedInHeaderAndBody(): void
    {
        $xml = $this->keyManagementResponse('000000', '000000');

        $sUT = new SymfonyEbicsServerCaller(new MockHttpClient(new MockResponse($xml)));

        self::assertSame($xml, $sUT->__invoke('test', new BankInfo('host', 'http://myurl.com', Version::v24(), 'partner', 'user')));
    }

    /** Cas reel CIC : en-tete 000000 [EBICS_OK] mais ordre INI / HIA refuse dans le corps. */
    public function testKeyManagementResponseRefusedInBody(): void
    {
        $sUT = new SymfonyEbicsServerCaller(new MockHttpClient(new MockResponse($this->keyManagementResponse('000000', '090004'))));

        try {
            $sUT->__invoke('test', new BankInfo('host', 'http://myurl.com', Version::v24(), 'partner', 'user'));
            self::fail('Le refus 090004 du corps aurait du lever une exception');
        } catch (InvalidOrderDataFormatException $exception) {
            self::assertSame('090004', $exception->getResponseCode());
            self::assertStringNotContainsString('EBICS_OK', $exception->getMessage());
            self::assertSame('test', $exception->getRequest());
        }
    }

    public function testKeyManagementResponseRefusedInHeader(): void
    {
        $sUT = new SymfonyEbicsServerCaller(new MockHttpClient(new MockResponse($this->keyManagementResponse('091002', '000000', '[EBICS_INVALID_USER_OR_USER_STATE] Subscriber unknown'))));

        self::expectException(InvalidUserOrUserStateException::class);
        $sUT->__invoke('test', new BankInfo('host', 'http://myurl.com', Version::v24(), 'partner', 'user'));
    }

    private function keyManagementResponse(string $headerCode, string $bodyCode, string $reportText = '[EBICS_OK] OK'): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><ebicsKeyManagementResponse xmlns="http://www.ebics.org/H003" Version="H003" Revision="1"><header authenticate="true"><static/><mutable><ReturnCode>' . $headerCode . '</ReturnCode><ReportText>' . $reportText . '</ReportText></mutable></header><body><ReturnCode authenticate="true">' . $bodyCode . '</ReturnCode></body></ebicsKeyManagementResponse>';
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

        $sUT = new SymfonyEbicsServerCaller($httpClient);

        $bank = self::createMock(BankInfo::class);

        $bank->expects(self::once())->method('getUrl')->willReturn('url');

        self::expectException(Throwable::class);
        $sUT->__invoke('test', $bank);
    }
}
