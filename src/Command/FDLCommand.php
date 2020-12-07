<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Command;

use DateTime;
use Fezfez\Ebics\BankInfo;
use Fezfez\Ebics\Crypt\BankPublicKeyDigest;
use Fezfez\Ebics\Crypt\DecryptOrderDataContent;
use Fezfez\Ebics\Crypt\EncrytSignatureValueWithUserPrivateKey;
use Fezfez\Ebics\DOMDocument;
use Fezfez\Ebics\EbicsServerCaller;
use Fezfez\Ebics\FDLParams;
use Fezfez\Ebics\KeyRing;
use Fezfez\Ebics\OrderDataEncrypted;
use Fezfez\Ebics\RenderXml;
use phpseclib\Crypt\Random;

use function base64_decode;
use function base64_encode;
use function bin2hex;
use function hash;
use function strtoupper;

class FDLCommand
{
    private RenderXml $renderXml;
    private EbicsServerCaller $ebicsServerCaller;
    private EncrytSignatureValueWithUserPrivateKey $cryptStringWithPasswordAndCertificat;
    private DecryptOrderDataContent $decryptOrderDataContent;
    private BankPublicKeyDigest $bankPublicKeyDigest;

    public function __construct(
        ?EbicsServerCaller $ebicsServerCaller = null,
        ?EncrytSignatureValueWithUserPrivateKey $cryptStringWithPasswordAndCertificat = null,
        ?RenderXml $renderXml = null
    ) {
        $this->ebicsServerCaller                    = $ebicsServerCaller ?? new EbicsServerCaller();
        $this->cryptStringWithPasswordAndCertificat = $cryptStringWithPasswordAndCertificat ?? new EncrytSignatureValueWithUserPrivateKey();
        $this->renderXml                            = $renderXml ?? new RenderXml();
        $this->decryptOrderDataContent              = new DecryptOrderDataContent();
        $this->bankPublicKeyDigest                  = new BankPublicKeyDigest();
    }

    public function __invoke(BankInfo $bank, KeyRing $keyRing, FDLParams $FDLParams, callable $handler, bool $sendRecip = false): void
    {
        $ebicsServerResponse = $this->callFDL($bank, $keyRing, $FDLParams);

        if ($ebicsServerResponse->getNodeValue('ReportText') === '[EBICS_OK] No download data available') {
            $handler(null);

            return;
        }

        $handler(
            $this->decryptOrderDataContent->__invoke(
                $keyRing,
                new OrderDataEncrypted(
                    $ebicsServerResponse->getNodeValue('OrderData'),
                    base64_decode($ebicsServerResponse->getNodeValue('TransactionKey'))
                )
            )
        );

        if (! $sendRecip) {
            return;
        }

        $this->callAknow($bank, $keyRing, $FDLParams, $ebicsServerResponse);
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

        $search['{{rawDigest}}']         = $this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'FDL_digest.xml');
        $search['{{DigestValue}}']       = base64_encode(hash('sha256', $search['{{rawDigest}}'], true));
        $search['{{RawSignatureValue}}'] = $this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'FDL_SignatureValue.xml');
        $search['{{SignatureValue}}']    = base64_encode(
            $this->cryptStringWithPasswordAndCertificat->__invoke(
                $keyRing,
                $keyRing->getUserCertificateX()->getPrivateKey(),
                hash('sha256', $search['{{RawSignatureValue}}'], true)
            )
        );

        return new DOMDocument(
            $this->ebicsServerCaller->__invoke($this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'FDL.xml'), $bank)
        );
    }

    private function callAknow(BankInfo $bank, KeyRing $keyRing, FDLParams $FDLParams, DOMDocument $response): DOMDocument
    {
        $search = [
            '{{TransactionID}}' => $response->getNodeValue('TransactionID'),
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

        $search['{{rawDigest}}']         = $this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'FDL_aknowledgement_digest.xml');
        $search['{{DigestValue}}']       = base64_encode(hash('sha256', $search['{{rawDigest}}'], true));
        $search['{{RawSignatureValue}}'] = $this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'FDL_aknowlgement_SignatureValue.xml');
        $search['{{SignatureValue}}']    = base64_encode(
            $this->cryptStringWithPasswordAndCertificat->__invoke(
                $keyRing,
                $keyRing->getUserCertificateX()->getPrivateKey(),
                hash('sha256', $search['{{RawSignatureValue}}'], true)
            )
        );

        return new DOMDocument(
            $this->ebicsServerCaller->__invoke($this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'FDL_acknowledgement.xml'), $bank)
        );
    }
}
