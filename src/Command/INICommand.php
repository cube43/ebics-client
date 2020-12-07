<?php

declare(strict_types=1);

namespace Fezfez\Ebics\Command;

use DateTime;
use Fezfez\Ebics\BankInfo;
use Fezfez\Ebics\CertificatType;
use Fezfez\Ebics\Crypt\GenerateCertificat;
use Fezfez\Ebics\EbicsServerCaller;
use Fezfez\Ebics\KeyRing;
use Fezfez\Ebics\RenderXml;
use Fezfez\Ebics\Version;
use Fezfez\Ebics\X509\X509CertificatOptionsGenerator;
use RuntimeException;

use function base64_encode;
use function Safe\gzcompress;

class INICommand
{
    private EbicsServerCaller $ebicsServerCaller;
    private GenerateCertificat $generateCertificat;
    private RenderXml $renderXml;

    public function __construct(
        ?EbicsServerCaller $ebicsServerCaller = null,
        ?GenerateCertificat $generateCertificat = null,
        ?RenderXml $renderXml = null
    ) {
        $this->ebicsServerCaller  = $ebicsServerCaller ?? new EbicsServerCaller();
        $this->generateCertificat = $generateCertificat ?? new GenerateCertificat();
        $this->renderXml          = $renderXml ?? new RenderXml();
    }

    public function __invoke(BankInfo $bank, KeyRing $keyRing, X509CertificatOptionsGenerator $x509CertificatOptionsGenerator, ?string $orderId = null): KeyRing
    {
        if ($orderId !== null && ! $bank->getVersion()->is(Version::v24())) {
            throw new RuntimeException('OrderID only avaiable on ebics 2.4');
        }

        if ($orderId === null && $bank->getVersion()->is(Version::v24())) {
            $orderId = 'A102';
        }

        $keyRing = $keyRing->setUserCertificateA($this->generateCertificat->__invoke($x509CertificatOptionsGenerator, $keyRing, CertificatType::a()));

        $search = [
            '{{TimeStamp}}' => (new DateTime())->format('Y-m-d\TH:i:s\Z'),
            '{{Modulus}}' => base64_encode($keyRing->getUserCertificateA()->getPublicKeyDetails()->getModulus()),
            '{{Exponent}}' => base64_encode($keyRing->getUserCertificateA()->getPublicKeyDetails()->getExponent()),
            '{{X509IssuerName}}' => $keyRing->getUserCertificateA()->getCertificatX509()->getInsurerName(),
            '{{X509SerialNumber}}' => $keyRing->getUserCertificateA()->getCertificatX509()->getSerialNumber(),
            '{{X509Certificate}}' => base64_encode($keyRing->getUserCertificateA()->getCertificatX509()->value()),
            '{{PartnerID}}' => $bank->getPartnerId(),
            '{{UserID}}' => $bank->getUserId(),
            '{{HostID}}' => $bank->getHostId(),
            '{{OrderID}}' => $orderId,
        ];

        $search['{{OrderData}}'] = base64_encode(gzcompress($this->renderXml->__invoke($search, $bank->getVersion(), 'INI_OrderData.xml')->toString()));

        $this->ebicsServerCaller->__invoke($this->renderXml->__invoke($search, $bank->getVersion(), 'INI.xml')->toString(), $bank);

        return $keyRing;
    }
}
