<?php

declare(strict_types=1);

namespace Fezfez\Ebics;

use Exception;
use Symfony\Component\HttpClient\HttpClient as SymfonyClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EbicsServerCaller
{
    private HttpClientInterface $httpClient;

    public function __construct(?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? SymfonyClient::create();
    }

    public function __invoke(string $request, BankInfo $bank): string
    {
        $result = $this->httpClient->request('POST', $bank->getUrl(), [
            'headers' => ['Content-Type' => 'text/xml; charset=ISO-8859-1'],
            'body' => $request,
            'verify_peer' => false,
            'verify_host' => false,
        ])->getContent();

        $resultXml = new DOMDocument($result);

        if ($resultXml->getNodeValue('ReturnCode') !== '000000') {
            throw new Exception('Error' . $resultXml->getNodeValue('ReportText'));
        }

        return $result;
    }
}
