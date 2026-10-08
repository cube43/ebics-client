<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics;

use Cube43\Component\Ebics\Exceptions\EbicsExceptionFactory;
use Symfony\Component\HttpClient\HttpClient as SymfonyClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function in_array;

final class SymfonyEbicsServerCaller implements EbicsServerCaller
{
    private const KEY_MANAGEMENT_RESPONSE = 'ebicsKeyManagementResponse';

    private readonly HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface|null $httpClient = null)
    {
        $this->httpClient = $httpClient ?? SymfonyClient::create();
    }

    /** @param string[] $expectedReturnCode */
    public function __invoke(string $request, BankInfo $bank, array $expectedReturnCode = ['000000']): string
    {
        $result = $this->httpClient->request('POST', $bank->getUrl(), [
            'headers' => ['Content-Type' => 'text/xml; charset=ISO-8859-1'],
            'body' => $request,
            'verify_peer' => false,
            'verify_host' => false,
        ])->getContent();

        $resultXml = new DOMDocument($result);

        if (! in_array($resultXml->getNodeValue('ReturnCode'), $expectedReturnCode)) {
            EbicsExceptionFactory::buildExceptionFromCode($resultXml->getNodeValue('ReturnCode'), $resultXml->getNodeValue('ReportText'), $request, $result);
        }

        // INI / HIA / HPB : la banque peut repondre 000000 dans l'en-tete (requete recue) et refuser l'ordre dans le corps
        // (ex. 090004 EBICS_INVALID_ORDER_DATA_FORMAT). Le ReportText de l'en-tete ("EBICS_OK") ne decrit pas cette erreur.
        if ($resultXml->getRootNodeName() === self::KEY_MANAGEMENT_RESPONSE) {
            $bodyReturnCode = $resultXml->getNodeValueChildOf('ReturnCode', 'body');

            if (! in_array($bodyReturnCode, $expectedReturnCode)) {
                EbicsExceptionFactory::buildExceptionFromCode($bodyReturnCode, null, $request, $result);
            }
        }

        return $result;
    }
}
