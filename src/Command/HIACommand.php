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

class HIACommand
{
    private EbicsServerCaller $httpClient;
    private GenerateCertificat $generateCertificat;
    private RenderXml $renderXml;

    public function __construct(
        ?EbicsServerCaller $httpClient = null,
        ?GenerateCertificat $generateCertificat = null,
        ?RenderXml $renderXml = null
    ) {
        $this->httpClient         = $httpClient ?? new EbicsServerCaller();
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

        $keyRing = $keyRing->setUserCertificateEAndX(
            $this->generateCertificat->__invoke($x509CertificatOptionsGenerator, $keyRing, CertificatType::e()),
            $this->generateCertificat->__invoke($x509CertificatOptionsGenerator, $keyRing, CertificatType::x())
        );

        $search = [
            '{{TimeStamp}}' => (new DateTime())->format('Y-m-d\TH:i:s\Z'),
            '{{CertUserE_Modulus}}' => base64_encode($keyRing->getUserCertificateE()->getPublicKeyDetails()->getModulus()),
            '{{CertUserE_Exponent}}' => base64_encode($keyRing->getUserCertificateE()->getPublicKeyDetails()->getExponent()),
            '{{CertUserE_X509IssuerName}}' => $keyRing->getUserCertificateE()->getCertificatX509()->getInsurerName(),
            '{{CertUserE_X509SerialNumber}}' => $keyRing->getUserCertificateE()->getCertificatX509()->getSerialNumber(),
            '{{CertUserE_X509Certificate}}' => base64_encode($keyRing->getUserCertificateE()->getCertificatX509()->value()),
            '{{CertUserX_Modulus}}' => base64_encode($keyRing->getUserCertificateX()->getPublicKeyDetails()->getModulus()),
            '{{CertUserX_Exponent}}' => base64_encode($keyRing->getUserCertificateX()->getPublicKeyDetails()->getExponent()),
            '{{CertUserX_X509IssuerName}}' => $keyRing->getUserCertificateX()->getCertificatX509()->getInsurerName(),
            '{{CertUserX_X509SerialNumber}}' => $keyRing->getUserCertificateX()->getCertificatX509()->getSerialNumber(),
            '{{CertUserX_X509Certificate}}' => base64_encode($keyRing->getUserCertificateX()->getCertificatX509()->value()),
            '{{PartnerID}}' => $bank->getPartnerId(),
            '{{UserID}}' => $bank->getUserId(),
            '{{HostID}}' => $bank->getHostId(),
            '{{OrderID}}' => $orderId,
        ];

        $search['{{OrderData}}'] = base64_encode(gzcompress($this->renderXml->__invoke($search, $bank->getVersion(), 'HIA_OrderData.xml')->toString()));

        $this->httpClient->__invoke($this->renderXml->__invoke($search, $bank->getVersion(), 'HIA.xml')->toString(), $bank);

        return $keyRing;
    }
}
