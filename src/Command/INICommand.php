<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Command;

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\CertificatType;
use Cube43\Component\Ebics\Crypt\GenerateCertificat;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\RenderXml;
use Cube43\Component\Ebics\SymfonyEbicsServerCaller;
use Cube43\Component\Ebics\Version;
use Cube43\Component\Ebics\X509\X509CertificatOptionsGenerator;
use DateTime;
use ErrorException;
use RuntimeException;

use function base64_encode;
use function gzcompress;

class INICommand
{
    private readonly EbicsServerCaller $ebicsServerCaller;
    private readonly GenerateCertificat $generateCertificat;
    private readonly RenderXml $renderXml;

    public function __construct(
        EbicsServerCaller|null $ebicsServerCaller = null,
        GenerateCertificat|null $generateCertificat = null,
        RenderXml|null $renderXml = null,
    ) {
        $this->ebicsServerCaller  = $ebicsServerCaller ?? new SymfonyEbicsServerCaller();
        $this->generateCertificat = $generateCertificat ?? new GenerateCertificat();
        $this->renderXml          = $renderXml ?? new RenderXml();
    }

    public function __invoke(BankInfo $bank, KeyRing $keyRing, X509CertificatOptionsGenerator $x509CertificatOptionsGenerator, string|null $orderId = null): KeyRing
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

        $search['{{OrderData}}'] = base64_encode(self::gzcompress($this->renderXml->__invoke($search, $bank->getVersion(), 'INI_OrderData.xml')->toString()));

        $this->ebicsServerCaller->__invoke($this->renderXml->__invoke($search, $bank->getVersion(), 'INI.xml')->toString(), $bank);

        return $keyRing;
    }

    private static function gzcompress(string $string): string
    {
        $safeResult = gzcompress($string);
        if ($safeResult === false) {
            throw new ErrorException('An error occured');
        }

        return $safeResult;
    }
}
