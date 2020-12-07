<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Command;

use DateTime;
use Fezfez\Ebics\BankCertificate;
use Fezfez\Ebics\BankInfo;
use Fezfez\Ebics\CertificateX509;
use Fezfez\Ebics\CertificatType;
use Fezfez\Ebics\Crypt\DecryptOrderDataContent;
use Fezfez\Ebics\Crypt\EncrytSignatureValueWithUserPrivateKey;
use Fezfez\Ebics\DOMDocument;
use Fezfez\Ebics\EbicsServerCaller;
use Fezfez\Ebics\KeyRing;
use Fezfez\Ebics\OrderDataEncrypted;
use Fezfez\Ebics\RenderXml;
use phpseclib\Crypt\Random;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

use function base64_decode;
use function base64_encode;
use function bin2hex;
use function hash;
use function strtoupper;

class HPBCommand
{
    private RenderXml $renderXml;
    private EbicsServerCaller $ebicsServerCaller;
    private EncrytSignatureValueWithUserPrivateKey $cryptStringWithPasswordAndCertificat;
    private DecryptOrderDataContent $decryptOrderDataContent;

    public function __construct(
        ?EbicsServerCaller $ebicsServerCaller = null,
        ?EncrytSignatureValueWithUserPrivateKey $cryptStringWithPasswordAndCertificat = null,
        ?RenderXml $renderXml = null
    ) {
        $this->ebicsServerCaller                    = $ebicsServerCaller ?? new EbicsServerCaller();
        $this->cryptStringWithPasswordAndCertificat = $cryptStringWithPasswordAndCertificat ?? new EncrytSignatureValueWithUserPrivateKey();
        $this->renderXml                            = $renderXml ?? new RenderXml();
        $this->decryptOrderDataContent              = new DecryptOrderDataContent();
    }

    public function __invoke(BankInfo $bank, KeyRing $keyRing): KeyRing
    {
        $search = [
            '{{HostID}}' => $bank->getHostId(),
            '{{Nonce}}' => strtoupper(bin2hex(Random::string(16))),
            '{{Timestamp}}' => (new DateTime())->format('Y-m-d\TH:i:s\Z'),
            '{{PartnerID}}' => $bank->getPartnerId(),
            '{{UserID}}' => $bank->getUserId(),
        ];

        $search['{{rawDigest}}']         = $this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'HPB_digest.xml');
        $search['{{DigestValue}}']       = base64_encode(hash('sha256', $search['{{rawDigest}}'], true));
        $search['{{RawSignatureValue}}'] = $this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'HPB_SignatureValue.xml');
        $search['{{SignatureValue}}']    = base64_encode(
            $this->cryptStringWithPasswordAndCertificat->__invoke(
                $keyRing,
                $keyRing->getUserCertificateX()->getPrivateKey(),
                hash('sha256', $search['{{RawSignatureValue}}'], true)
            )
        );

        $ebicsServerResponse = new DOMDocument(
            $this->ebicsServerCaller->__invoke($this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'HPB.xml'), $bank)
        );

        $decryptedResponse = $this->decryptOrderDataContent->__invoke(
            $keyRing,
            new OrderDataEncrypted(
                $ebicsServerResponse->getNodeValue('OrderData'),
                base64_decode($ebicsServerResponse->getNodeValue('TransactionKey'))
            )
        );

        $decryptedResponse = new DOMDocument($decryptedResponse);

        return $keyRing->setBankCertificate(
            $this->cert($decryptedResponse, CertificatType::x(), 'AuthenticationPubKeyInfo'),
            $this->cert($decryptedResponse, CertificatType::e(), 'EncryptionPubKeyInfo'),
        );
    }

    private function cert(DOMDocument $decrypted, CertificatType $certificatType, string $parentNode): BankCertificate
    {
        $rsa = new RSA();
        $rsa->loadKey([
            'n' => new BigInteger(base64_decode($decrypted->getNodeValueChildOf('Modulus', $parentNode)), 256),
            'e' => new BigInteger(base64_decode($decrypted->getNodeValueChildOf('Exponent', $parentNode)), 256),
        ]);

        return new BankCertificate(
            $certificatType,
            $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1),
            new CertificateX509(bin2hex(base64_decode($decrypted->getNodeValueChildOf('X509Certificate', $parentNode))))
        );
    }
}
