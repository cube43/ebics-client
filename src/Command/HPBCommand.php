<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Command;

use Cube43\Component\Ebics\BankCertificate;
use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\CertificateX509;
use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\Crypt\DecryptOrderDataContent;
use Cube43\Component\Ebics\Crypt\EncrytSignatureValueWithUserPrivateKey;
use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\OrderDataEncrypted;
use Cube43\Component\Ebics\RenderXml;
use DateTime;
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
    private readonly RenderXml $renderXml;
    private readonly EbicsServerCaller $ebicsServerCaller;
    private readonly EncrytSignatureValueWithUserPrivateKey $cryptStringWithPasswordAndCertificat;
    private readonly DecryptOrderDataContent $decryptOrderDataContent;

    public function __construct(
        EbicsServerCaller|null $ebicsServerCaller = null,
        EncrytSignatureValueWithUserPrivateKey|null $cryptStringWithPasswordAndCertificat = null,
        RenderXml|null $renderXml = null,
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
                hash('sha256', $search['{{RawSignatureValue}}'], true),
            ),
        );

        $ebicsServerResponse = new DOMDocument(
            $this->ebicsServerCaller->__invoke($this->renderXml->renderXmlRaw($search, $bank->getVersion(), 'HPB.xml'), $bank),
        );

        $decryptedResponse = $this->decryptOrderDataContent->__invoke(
            $keyRing,
            new OrderDataEncrypted(
                $ebicsServerResponse->getNodeValue('OrderData'),
                base64_decode($ebicsServerResponse->getNodeValue('TransactionKey')),
            ),
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
            new CertificateX509(bin2hex(base64_decode($decrypted->getNodeValueChildOf('X509Certificate', $parentNode)))),
        );
    }
}
