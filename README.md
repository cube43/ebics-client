# cube43/ebics-client

PHP library to communicate with bank through EBICS protocol.

![Infection](https://github.com/cube43/ebics-client/workflows/Infection/badge.svg)
![Phpcs](https://github.com/cube43/ebics-client/workflows/PHPcs/badge.svg)
![Phpstan](https://github.com/cube43/ebics-client/workflows/PHPStan/badge.svg)
![Phpunit](https://github.com/cube43/ebics-client/workflows/PHPUnit/badge.svg)
![Psalm](https://github.com/cube43/ebics-client/workflows/Psalm/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/cube43/ebics-client/v/stable)](https://packagist.org/packages/cube43/ebics-client)
[![Total Downloads](https://poser.pugx.org/cube43/ebics-client/downloads)](https://packagist.org/packages/cube43/ebics-client)
[![License](https://poser.pugx.org/cube43/ebics-client/license)](https://packagist.org/packages/cube43/ebics-client)

## License
cube43/ebics-client is licensed under the MIT License, see the LICENSE file for details

## Note
This library is a refactoring of andrew-svirin/ebics-client-php to allow multiple protocol version + unit test and E2e test

Ebics protocol version supported :

- 2.4
- 2.5
- 3.0

Command supported :

- INI
- HIA
- HPB
- FDL

This library work only with X509 certified communication

## Installation

```bash
composer require cube43/ebics-client
```


## Initialize client

You will need to have this informations from your Bank : 

- HostID
- HostURL
- PartnerID
- UserID
- protocol version

```php
$bankInfo            = new \Cube43\Component\Ebics\BankInfo($HOST_ID, $HOST_URL, \Cube43\Component\Ebics\Version::v24(), $PARTNER_ID, $USER_ID);
$keyring             = new \Cube43\Component\Ebics\KeyRing('myPassword');
$x509OptionGenerator = new \Cube43\Component\Ebics\X509\DefaultX509OptionGenerator();
```

**Note** : $HOST_ID, $HOST_URL, $PARTNER_ID, $USER_ID and version are decided between you and your bank.

## How to use

Before making what you want to achieve (ex: FDL call) you have to generate keys and communicate it to  with the server (INI, HIA and HPB command).

## INI command

INI command will generate a certificat of type A and send it to ebics server.
After making this request, you have to save the keyring with the new generate certificat because it will be used in call after.

```php
$keyring = (new \Cube43\Component\Ebics\Command\INICommand())->__invoke($bankInfo, $keyring, $x509OptionGenerator);
// save keyring
```

## HIA command

HIA command will generate a certificat of type e and x and then send it to ebics server.
After making this request, you have to save the keyring with the new generate certificat because it will be used in call after.

```php
$keyring = (new \Cube43\Component\Ebics\Command\HIACommand())->__invoke($bankInfo, $keyring, $x509OptionGenerator);
// save keyring
```

## HPB command

HPB command will retrieve certificat of type e and x from the ebics server.
After making this request, you have to save the keyring with the new retrieved certificat because it will be used in call after.

```php
$keyring = (new \Cube43\Component\Ebics\Command\HPBCommand())->__invoke($bankInfo, $keyring);
// save keyring
```

Once INI, HIA and HPB have been run your good to use ebics protocol.

## FDL command

```php
<?php

$keyring = (new \Cube43\Component\Ebics\Command\FDLCommand())->__invoke($bankInfo, $keyring, new FDLParams($fdlFromBank, 'FR', new DateTimeImmutable(), new DateTimeImmutable()), function (string $data = null): void {
    if ($data === null) {
        var_dump('no file');
    } else {
        var_dump('file : ', $file);
    }
});

```

## Saving keyring

```php
<?php

$keyring = new \Cube43\Component\Ebics\KeyRing('myPassword');
$keyringAsArray = $keyring->jsonSerialize(); 
$keyringAsJson  = json_encode($keyring); 

// put $keyringAsArray or $keyringAsJson in db, file etc...

```

## Wakeup keyring

```php
$keyring = \Cube43\Component\Ebics\KeyRing::fromArray($keyringAsArray, 'myPassword');

```

## good to know

This website provide an ebics server testing environnement : https://software.elcimai.com/efs/accueil-qualif.jsp 


# Full sample to generate certificate and get letter

```php
<?php

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\Command\INICommand;
use Cube43\Component\Ebics\Command\HIACommand;
use Cube43\Component\Ebics\X509\DefaultX509OptionGenerator;
use Cube43\Component\Ebics\Version;

require 'vendor/autoload.php';

$rsaPassword = '';
$bank        = new BankInfo('EBIXQUAL', 'https://server-ebics.webank.fr:28103/WbkPortalFileTransfert/EbicsProtocol', Version::v24(), 'MYPARTID', 'MYUSERID');
$keyRing     = KeyRing::fromFile('keyring.json', $rsaPassword);

$x509OptionGenerator = new DefaultX509OptionGenerator();


if (!$keyRing->hasUserCertificatA()) {
    $keyRing = (new INICommand())->__invoke($bank, $keyRing, $x509OptionGenerator);
    file_put_contents('keyring.json', json_encode($keyRing));
}

if (!$keyRing->hasUserCertificateEAndX()) {
    $keyRing = (new HIACommand())->__invoke($bank, KeyRing::fromFile('keyring.json', $rsaPassword), $x509OptionGenerator);
    file_put_contents('keyring.json', json_encode($keyRing));
}

if (!$keyRing->hasUserCertificatA() || !$keyRing->hasUserCertificateEAndX()) {
    echo 'Cant generate letter';
    return;
}
echo '
<table class="table table-borderless">
    <tbody>
        <tr>
            <td>User ID</td>
            <td>'.$bank->getUserId().'</td>
        </tr>
        <tr>
            <td>PartnerID</td>
            <td>'.$bank->getPartnerId().'</td>
        </tr>
        <tr>
            <td>Hash '.$keyRing->getUserCertificateA()->getCertificatType()->toString().' (SHA-256)</td>
            <td>
                <div class="digest">'.nl2br($keyRing->getUserCertificateA()->getCertificatX509()->hash()).'</div>
            </td>
        </tr>
        <tr>
            <td>Fingerprint '.$keyRing->getUserCertificateA()->getCertificatType()->toString().'  (SHA-256)</td>
            <td>
                <div class="digest">'.nl2br($keyRing->getUserCertificateA()->getCertificatX509()->fingerprint()).'</div>
            </td>
        </tr>
        <tr>
            <td>Hash '.$keyRing->getUserCertificateE()->getCertificatType()->toString().' (SHA-256)</td>
            <td>
                <div class="digest">'.nl2br($keyRing->getUserCertificateE()->getCertificatX509()->hash()).'</div>
            </td>
        </tr>
        <tr>
            <td>Fingerprint '.$keyRing->getUserCertificateE()->getCertificatType()->toString().'  (SHA-256)</td>
            <td>
                <div class="digest">'.nl2br($keyRing->getUserCertificateE()->getCertificatX509()->fingerprint()).'</div>
            </td>
        </tr>
        <tr>
            <td>Hash '.$keyRing->getUserCertificateX()->getCertificatType()->toString().' (SHA-256)</td>
            <td>
                <div class="digest">'.nl2br($keyRing->getUserCertificateX()->getCertificatX509()->hash()).'</div>
            </td>
        </tr>
        <tr>
            <td>Fingerprint '.$keyRing->getUserCertificateX()->getCertificatType()->toString().'  (SHA-256)</td>
            <td>
                <div class="digest">'.nl2br($keyRing->getUserCertificateX()->getCertificatX509()->fingerprint()).'</div>
            </td>
        </tr>
    </tbody>
</table>
';

```

# Working with Doctrine2 instead of file

```php 
/**
 * @ORM\Table(name="ebics")
 * @ORM\Entity()
 *
 * @Type()
 */
class Ebics
{
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id
     */
    private UuidInterface $id;
    /** @ORM\Column(type="string") */
    private string $hostUrl;
    /** @ORM\Column(type="string") */
    private string $partnerId;
    /** @ORM\Column(type="string") */
    private string $userId;
    /** @ORM\Column(type="string") */
    private string $hostId;
    /** @ORM\Column(type="json") */
    private array $certificat;

    public function __construct(
        string $hostUrl,
        string $hostId,
        string $partnerId,
        string $userId
    ) {
        $this->id           = Uuid::uuiv4();
        $this->hostUrl      = $hostUrl;
        $this->partnerId    = $partnerId;
        $this->userId       = $userId;
        $this->hostId       = $hostId;
        $this->certificat   = [];
    }

    public function getKeyring(): KeyRing
    {
        return KeyRing::fromArray($this->getCertificat(), (string) getenv('PASSWORD'));
    }

    public function getBank(): Bank
    {
        return new Bank($this->getHostId(), $this->getHostUrl(), Version::v24(), $this->getPartnerId(), $this->getUserId());
    }
    
    public function setCertificat(KeyRing $keyRing): void
    {
        $this->certificat = json_decode(json_encode($keyRing->jsonSerialize()), true);
    }

}



$ebicsEntity         = new Ebics('EBIXQUAL', 'https://server-ebics.webank.fr:28103/WbkPortalFileTransfert/EbicsProtocol', Version::v24(), $partnerId, $userId);
$x509OptionGenerator = new DefaultX509OptionGenerator();


if (!$ebicsEntity->getKeyring()->hasUserCertificatA()) {
    $ebicsEntity->setCertificat((new INICommand())->__invoke($ebicsEntity->getBank(), $ebicsEntity->getKeyring(), $x509OptionGenerator));
}

if (!$ebicsEntity->getKeyring()->hasUserCertificateEAndX()) {
    $ebicsEntity->setCertificat((new HIACommand())->__invoke($ebicsEntity->getBank(), $ebicsEntity->getKeyring(), $x509OptionGenerator));
}

if (!$ebicsEntity->getKeyring()->hasBankCertificate()) {
    $ebicsEntity->setCertificat((new HPBCommand())->__invoke($ebicsEntity->getBank(), $ebicsEntity->getKeyring()));
}

``