<?php

declare(strict_types=1);

namespace Fezfez\Ebics\X509;

use Fezfez\Ebics\CertificatType;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;
use RuntimeException;

use function array_merge;
use function is_array;
use function rand;
use function sprintf;
use function var_export;

class X509Generator
{
    public function __invoke(RSA $privateKey, RSA $publicKey, CertificatType $type, X509CertificatOptionsGenerator $certificatOptionsGenerator): string
    {
        $options = array_merge([
            'type' => $type->value(),
            'subject' => [
                'domain' => null,
                'DN' => null,
            ],
            'issuer' => ['DN' => null], //Same as subject, means self-signed
            'extensions' => [],
        ], $certificatOptionsGenerator->getOption());

        $subject            = $this->generateSubject($publicKey, $options);
        $issuer             = $this->generateIssuer($privateKey, $publicKey, $subject, $options);
        $x509               = new X509();
        $x509->startDate    = $certificatOptionsGenerator->getStart()->format('YmdHis');
        $x509->endDate      = $certificatOptionsGenerator->getEnd()->format('YmdHis');
        $x509->serialNumber = $this->generateSerialNumber();

        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');
        $x509->loadX509($result);

        foreach ($options['extensions'] as $id => $extension) {
            $extension = self::normalize($extension);

            if ($x509->setExtension($id, $extension['value'], $extension['critical'], $extension['replace']) === false) {
                throw new RuntimeException(sprintf('Unable to set "%s" extension with value: %s', $id, var_export($extension['value'], true)));
            }
        }

        $result = $x509->sign($issuer, $x509, 'sha256WithRSAEncryption');

        return $x509->saveX509($result);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function generateSubject(RSA $publicKey, array $options): X509
    {
        $subject = new X509();
        $subject->setPublicKey($publicKey); // $pubKey is Crypt_RSA object

        if (! empty($options['subject']['DN'])) {
            $subject->setDN($options['subject']['DN']);
        }

        if (! empty($options['subject']['domain'])) {
            $subject->setDomain($options['subject']['domain']);
        }

        $subject->setKeyIdentifier($subject->computeKeyIdentifier($publicKey)); // id-ce-subjectKeyIdentifier

        return $subject;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function generateIssuer(RSA $privateKey, RSA $publicKey, X509 $subject, array $options): X509
    {
        $issuer = new X509();
        $issuer->setPrivateKey($privateKey); // $privKey is Crypt_RSA object

        if (! empty($options['issuer']['DN'])) {
            $issuer->setDN($options['issuer']['DN']);
        } else {
            $issuer->setDN($subject->getDN());
        }

        $issuer->setKeyIdentifier($subject->computeKeyIdentifier($publicKey));

        return $issuer;
    }

    /**
     * Generate 74 digits serial number represented in the string.
     */
    private function generateSerialNumber(): string
    {
        // prevent the first number from being 0
        $result = rand(1, 9);
        for ($i = 0; $i < 74; ++$i) {
            $result .= rand(0, 9);
        }

        return (string) $result;
    }

    /**
     * @see X509::setExtension()
     *
     * @param mixed|string|array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private static function normalize($options): array
    {
        $value    = null;
        $critical = false;
        $replace  = true;

        if (! is_array($options)) {
            $value = $options;
        } else {
            if (! isset($options['value'])) {
                $value = $options;
            } else {
                $value = $options['value'];
                if (isset($options['critical'])) {
                    $critical = $options['critical'];
                }

                if (isset($options['replace'])) {
                    $replace = $options['replace'];
                }
            }
        }

        return [
            'value' => $value,
            'critical' => $critical,
            'replace' => $replace,
        ];
    }
}
