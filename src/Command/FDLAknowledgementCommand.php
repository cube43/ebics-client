<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Command;

use Cube43\Component\Ebics\Crypt\BankPublicKeyDigest;
use Cube43\Component\Ebics\Crypt\SignQuery;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\RenderXml;
use DateTime;
use phpseclib\Crypt\Random;

use function bin2hex;
use function strtoupper;

class FDLAknowledgementCommand
{
    private readonly RenderXml $renderXml;
    private readonly EbicsServerCaller $ebicsServerCaller;
    private readonly BankPublicKeyDigest $bankPublicKeyDigest;
    private readonly SignQuery|null $signQuery;

    public function __construct(
        EbicsServerCaller|null $ebicsServerCaller = null,
        RenderXml|null $renderXml = null,
        SignQuery|null $signQuery = null,
    ) {
        $this->ebicsServerCaller   = $ebicsServerCaller ?? new EbicsServerCaller();
        $this->renderXml           = $renderXml ?? new RenderXml();
        $this->bankPublicKeyDigest = new BankPublicKeyDigest();
        $this->signQuery           = $signQuery ?? new SignQuery();
    }

    public function __invoke(FDLResponse $FDLResponse): void
    {
        $search = [
            '{{TransactionID}}' => $FDLResponse->serverResponse->getLastNodeValue('TransactionID'),
            '{{HostID}}' => $FDLResponse->bank->getHostId(),
            '{{Nonce}}' => strtoupper(bin2hex(Random::string(16))),
            '{{Timestamp}}' => (new DateTime())->format('Y-m-d\TH:i:s\Z'),
            '{{PartnerID}}' => $FDLResponse->bank->getPartnerId(),
            '{{UserID}}' => $FDLResponse->bank->getUserId(),
            '{{BankPubKeyDigestsEncryption}}' => $this->bankPublicKeyDigest->__invoke($FDLResponse->keyRing->getBankCertificateE()),
            '{{BankPubKeyDigestsAuthentication}}' => $this->bankPublicKeyDigest->__invoke($FDLResponse->keyRing->getBankCertificateX()),
            '{{FileFormat}}' => $FDLResponse->FDLParams->fileFormat(),
            '{{CountryCode}}' => $FDLResponse->FDLParams->countryCode(),
        ];

        $this->ebicsServerCaller->__invoke(
            $this->signQuery->__invoke(
                $this->renderXml->__invoke($search, $FDLResponse->bank->getVersion(), 'FDL_acknowledgement.xml'),
                $FDLResponse->keyRing,
            )->getFormattedContent(),
            $FDLResponse->bank
        );
    }
}
