<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Command;

use Cube43\Component\Ebics\Crypt\BankPublicKeyDigest;
use Cube43\Component\Ebics\Crypt\EncrytSignatureValueWithUserPrivateKey;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\RenderXml;
use DateTime;
use phpseclib\Crypt\Random;

use function base64_encode;
use function bin2hex;
use function hash;
use function strtoupper;

class FDLAknowledgementCommand
{
    private readonly RenderXml $renderXml;
    private readonly EbicsServerCaller $ebicsServerCaller;
    private readonly EncrytSignatureValueWithUserPrivateKey $cryptStringWithPasswordAndCertificat;
    private readonly BankPublicKeyDigest $bankPublicKeyDigest;

    public function __construct(
        EbicsServerCaller|null $ebicsServerCaller = null,
        EncrytSignatureValueWithUserPrivateKey|null $cryptStringWithPasswordAndCertificat = null,
        RenderXml|null $renderXml = null,
    ) {
        $this->ebicsServerCaller                    = $ebicsServerCaller ?? new EbicsServerCaller();
        $this->cryptStringWithPasswordAndCertificat = $cryptStringWithPasswordAndCertificat ?? new EncrytSignatureValueWithUserPrivateKey();
        $this->renderXml                            = $renderXml ?? new RenderXml();
        $this->bankPublicKeyDigest                  = new BankPublicKeyDigest();
    }

    public function __invoke(FDLResponse $FDLResponse): void
    {
        $search = [
            '{{TransactionID}}' => $FDLResponse->serverResponse->getNodeValue('TransactionID'),
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

        $search['{{rawDigest}}']         = $this->renderXml->renderXmlRaw($search, $FDLResponse->bank->getVersion(), 'FDL_aknowledgement_digest.xml');
        $search['{{DigestValue}}']       = base64_encode(hash('sha256', $search['{{rawDigest}}'], true));
        $search['{{RawSignatureValue}}'] = $this->renderXml->renderXmlRaw($search, $FDLResponse->bank->getVersion(), 'FDL_aknowlgement_SignatureValue.xml');
        $search['{{SignatureValue}}']    = base64_encode(
            $this->cryptStringWithPasswordAndCertificat->__invoke(
                $FDLResponse->keyRing,
                $FDLResponse->keyRing->getUserCertificateX()->getPrivateKey(),
                hash('sha256', $search['{{RawSignatureValue}}'], true),
            ),
        );

        $this->ebicsServerCaller->__invoke($this->renderXml->renderXmlRaw($search, $FDLResponse->bank->getVersion(), 'FDL_acknowledgement.xml'), $FDLResponse->bank);
    }
}
