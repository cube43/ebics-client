<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Command;

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\Crypt\BankPublicKeyDigest;
use Cube43\Component\Ebics\Crypt\DecryptOrderDataContent;
use Cube43\Component\Ebics\Crypt\EncrytSignatureValueWithUserPrivateKey;
use Cube43\Component\Ebics\Crypt\SignQuery;
use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\FDLParams;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\OrderDataEncrypted;
use Cube43\Component\Ebics\RenderXml;
use DateTime;
use phpseclib\Crypt\Random;
use RuntimeException;

use function base64_decode;
use function base64_encode;
use function bin2hex;
use function hash;
use function in_array;
use function strtoupper;

class FDLCommand
{
    private const NO_DATA = '090005';

    private readonly RenderXml $renderXml;
    private readonly EbicsServerCaller $ebicsServerCaller;
    private readonly EncrytSignatureValueWithUserPrivateKey $cryptStringWithPasswordAndCertificat;
    private readonly DecryptOrderDataContent $decryptOrderDataContent;
    private readonly BankPublicKeyDigest $bankPublicKeyDigest;
    private readonly SignQuery|null $signQuery;

    public function __construct(
        EbicsServerCaller|null $ebicsServerCaller = null,
        EncrytSignatureValueWithUserPrivateKey|null $cryptStringWithPasswordAndCertificat = null,
        RenderXml|null $renderXml = null,
        SignQuery|null $signQuery = null,
    ) {
        $this->ebicsServerCaller                    = $ebicsServerCaller ?? new EbicsServerCaller();
        $this->cryptStringWithPasswordAndCertificat = $cryptStringWithPasswordAndCertificat ?? new EncrytSignatureValueWithUserPrivateKey();
        $this->renderXml                            = $renderXml ?? new RenderXml();
        $this->decryptOrderDataContent              = new DecryptOrderDataContent();
        $this->bankPublicKeyDigest                  = new BankPublicKeyDigest();
        $this->signQuery           = $signQuery ?? new SignQuery();
    }

    public function __invoke(BankInfo $bank, KeyRing $keyRing, FDLParams $FDLParams): FDLResponse
    {
        $ebicsServerResponse = $this->callFDL($bank, $keyRing, $FDLParams);

        if (in_array(self::NO_DATA, $this->findAllReturnCode($ebicsServerResponse))) {
            return new FDLResponse($bank, $keyRing, $FDLParams, $ebicsServerResponse, null);
        }

        return new FDLResponse(
            $bank,
            $keyRing,
            $FDLParams,
            $ebicsServerResponse,
            $this->decryptOrderDataContent->__invoke(
                $keyRing,
                new OrderDataEncrypted(
                    $ebicsServerResponse->getNodeValue('OrderData'),
                    base64_decode($ebicsServerResponse->getNodeValue('TransactionKey')),
                ),
            ),
        );
    }

    private function callFDL(BankInfo $bank, KeyRing $keyRing, FDLParams $FDLParams): DOMDocument
    {
        $search = [
            '{{StartDate}}' => $FDLParams->getStartDate()->format('Y-m-d'),
            '{{EndDate}}' => $FDLParams->getEndDate()->format('Y-m-d'),
            '{{HostID}}' => $bank->getHostId(),
            '{{Nonce}}' => strtoupper(bin2hex(Random::string(16))),
            '{{Timestamp}}' => (new DateTime())->format('Y-m-d\TH:i:s\Z'),
            '{{PartnerID}}' => $bank->getPartnerId(),
            '{{UserID}}' => $bank->getUserId(),
            '{{BankPubKeyDigestsEncryption}}' => $this->bankPublicKeyDigest->__invoke($keyRing->getBankCertificateE()),
            '{{BankPubKeyDigestsAuthentication}}' => $this->bankPublicKeyDigest->__invoke($keyRing->getBankCertificateX()),
            '{{FileFormat}}' => $FDLParams->fileFormat(),
            '{{CountryCode}}' => $FDLParams->countryCode(),
        ];

        $xml = $this->signQuery->__invoke(
            $this->renderXml->__invoke($search, $bank->getVersion(), 'FDL.xml'),
            $keyRing,
        )->getFormattedContent();

        return new DOMDocument(
            $this->ebicsServerCaller->__invoke($xml, $bank),
        );
    }

    /** @return array<int, string> */
    private function findAllReturnCode(DOMDocument $ebicsServerResponse): array
    {
        $returnCode = [];

        try {
            $returnCode[] = $ebicsServerResponse->getNodeValue('ReturnCode');
        } catch (RuntimeException) {
        }

        try {
            $returnCode[] = $ebicsServerResponse->getNodeValue('ReturnCode', 1);
        } catch (RuntimeException) {
        }

        return $returnCode;
    }
}
