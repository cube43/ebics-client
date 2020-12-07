# ebics

PHP library to communicate with bank through EBICS protocol.

## License
fezfez/ebics is licensed under the MIT License, see the LICENSE file for details

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
composer require fezfez/ebics
```


## Initialize client

You will need to have this informations from your Bank : 

- HostID
- HostURL
- PartnerID
- UserID
- protocol version

```php
<?php

$bankInfo            = new \Fezfez\Ebics\BankInfo($HOST_ID, $HOST_URL, \Fezfez\Ebics\Version::v24(), $PARTNER_ID, $USER_ID);
$keyring             = new \Fezfez\Ebics\KeyRing('myPassword');
$x509OptionGenerator = new \Fezfez\Ebics\X509\DefaultX509OptionGenerator();
```

**Note** : $HOST_ID, $HOST_URL, $PARTNER_ID, $USER_ID and version are decided between you and your bank.

## How to use

Before making what you want to achieve (ex: FDL call) you have to generate keys and communicate it to  with the server (INI, HIA and HPB command).

## INI command

INI command will generate a certificat of type A and send it to ebics server.
After making this request, you have to save the keyring with the new generate certificat because it will be used in call after.

```php
<?php

$keyring = (new \Fezfez\Ebics\Command\INICommand())->__invoke($bankInfo, $keyring, $x509OptionGenerator);
// save kering

```

## HIA command

HIA command will generate a certificat of type e and x and then send it to ebics server.
After making this request, you have to save the keyring with the new generate certificat because it will be used in call after.

```php
<?php

$keyring = (new \Fezfez\Ebics\Command\HIACommand())->__invoke($bankInfo, $keyring, $x509OptionGenerator);
// save kering 

```

## HPB command

HPB command will retrieve certificat of type e and x from the ebics server.
After making this request, you have to save the keyring with the new retrieved certificat because it will be used in call after.

```php
<?php

$keyring = (new \Fezfez\Ebics\Command\HPBCommand())->__invoke($bankInfo, $keyring);
// save kering

```

Once INI, HIA and HPB have been run your good to use ebics protocol.

## Saving keyring

```php
<?php

$keyring = new \Fezfez\Ebics\KeyRing('myPassword');
$keyringAsArray = $keyring->jsonSerialize(); 
$keyringAsJson  = json_encode($keyring); 

// put $keyringAsArray or $keyringAsJson in db, file etc...

```

## Wakeup keyring

```php
<?php

$keyring = \Fezfez\Ebics\KeyRing::fromArray($keyringAsArray, 'myPassword');

```

## good to know

This website provide an ebics server testing environnement : https://software.elcimai.com/efs/accueil-qualif.jsp 


# Full sample to generate certificat and get letter

```php
<?php

use Fezfez\Ebics\BankInfo;
use Fezfez\Ebics\KeyRing;
use Fezfez\Ebics\Command\INICommand;
use Fezfez\Ebics\Command\HIACommand;
use Fezfez\Ebics\Command\HPBCommand;
use Fezfez\Ebics\X509\DefaultX509OptionGenerator;
use Fezfez\Ebics\Version;

require 'vendor/autoload';

$bank                = new BankInfo('EBIXQUAL', 'https://server-ebics.webank.fr:28103/WbkPortalFileTransfert/EbicsProtocol', Version::v24(), $partnerId, $userId);
$keyRing             = KeyRing::fromFile('keyring.json', 'myPassword');
$x509OptionGenerator = new DefaultX509OptionGenerator();


if (!$keyRing->hasUserCertificatA()) {
    $keyring = (new INICommand())->__invoke($bank, $keyRing, $x509OptionGenerator);
    file_put_contents('keyring.json', json_encode($keyring));
}

if (!$keyRing->hasUserCertificateEAndX()) {
    $keyring = (new HIACommand())->__invoke($bank, KeyRing::fromFile('keyring.json', 'myPassword'), $x509OptionGenerator);
    file_put_contents('keyring.json', json_encode($keyring));
}

if (!$keyRing->hasBankCertificate()) {
    $keyring = (new HPBCommand())->__invoke($bank, KeyRing::fromFile('keyring.json', 'myPassword'));
    file_put_contents('keyring.json', json_encode($keyring));
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
            <td>Hash '.$keyring->getUserCertificateA()->getCertificatType()->toString().' ('.$keyring->getUserCertificateA()->getCertificatType()->getHash().')</td>
            <td>
                <div class="digest">'.nl2br($keyring->getUserCertificateA()->getCertificatX509()->digest()).'</div>
            </td>
        </tr>
        <tr>
            <td>Hash '.$keyring->getUserCertificateE()->getCertificatType()->toString().' ('.$keyring->getUserCertificateE()->getCertificatType()->getHash().')</td>
            <td>
                <div class="digest">'.nl2br($keyring->getUserCertificateE()->getCertificatX509()->digest()).'</div>
            </td>
        </tr>
        <tr>
            <td>Hash '.$keyring->getUserCertificateX()->getCertificatType()->toString().' ('.$keyring->getUserCertificateX()->getCertificatType()->getHash().')</td>
            <td>
                <div class="digest">'.nl2br($keyring->getUserCertificateX()->getCertificatX509()->digest()).'</div>
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