<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Command;

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\CertificateType;
use Cube43\Component\Ebics\Crypt\GenerateCertificat;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\RenderXml;
use Cube43\Component\Ebics\Version;
use Cube43\Component\Ebics\X509\X509CertificatOptionsGenerator;
use DateTime;
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

        $keyRing = $keyRing->setUserCertificateA($this->generateCertificat->__invoke($x509CertificatOptionsGenerator, $keyRing, CertificateType::a()));

        $search = [
            '{{TimeStamp}}' => (new DateTime())->format('Y-m-d\TH:i:s\Z'),
            '{{Modulus}}' => base64_encode($keyRing->getUserCertificateA()->getPublicKey()->getExponentAndModulus()->getModulus()),
            '{{Exponent}}' => base64_encode($keyRing->getUserCertificateA()->getPublicKey()->getExponentAndModulus()->getExponent()),
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
