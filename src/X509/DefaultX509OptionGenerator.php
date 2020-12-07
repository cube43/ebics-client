<?php

declare(strict_types=1);

namespace Fezfez\Ebics\X509;

use DateTimeImmutable;

class DefaultX509OptionGenerator implements X509CertificatOptionsGenerator
{
    /** @psalm-pure */
    public function getOption(): array
    {
        return [
            'subject' => [
                'domain' => 'silarhi.fr',
                'DN' => [
                    'id-at-countryName' => 'FR',
                    'id-at-stateOrProvinceName' => 'Occitanie',
                    'id-at-localityName' => 'Toulouse',
                    'id-at-organizationName' => 'SILARHI',
                    'id-at-commonName' => 'silarhi.fr',
                ],
            ],
            'extensions' => [
                'id-ce-subjectAltName' => [
                    'value' => [
                        ['dNSName' => '*.silarhi.fr'],
                    ],
                ],
                'id-ce-basicConstraints' => [
                    'value' => ['CA' => false],
                ],
                'id-ce-keyUsage' => [
                    'value' => ['keyEncipherment', 'digitalSignature', 'nonRepudiation'],
                    'critical' => true,
                ],
                'id-ce-extKeyUsage' => [
                    'value' => ['id-kp-serverAuth', 'id-kp-clientAuth'],
                ],
            ],
        ];
    }

    /** @psalm-pure */
    public function getStart(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->modify('-1 days');
    }

    /** @psalm-pure */
    public function getEnd(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->modify('+1 year');
    }
}
