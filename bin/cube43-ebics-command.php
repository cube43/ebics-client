<?php

declare(strict_types=1);

use Cube43\Component\Ebics\BankInfo;
use Cube43\Component\Ebics\Command\HIACommand;
use Cube43\Component\Ebics\Command\HPBCommand;
use Cube43\Component\Ebics\Command\INICommand;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\Version;
use Cube43\Component\Ebics\X509\DefaultX509OptionGenerator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

require __DIR__ . '/../vendor/autoload.php';


class DefaultCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('INI,HIA,HPB');
        $this->addArgument('partnerId', InputArgument::REQUIRED);
        $this->addArgument('userId', InputArgument::REQUIRED);
        $this->addArgument('hostId', InputArgument::OPTIONAL, '', 'EBIXQUAL');
        $this->addArgument('url', InputArgument::OPTIONAL, '', 'https://server-ebics.webank.fr:28103/WbkPortalFileTransfert/EbicsProtocol');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $partnerId = $this->mustBeString('partnerId', $input->getArgument('partnerId'));
        $userId    = $this->mustBeString('userId', $input->getArgument('userId'));
        $hostId    = $this->mustBeString('hostId', $input->getArgument('hostId'));
        $url       = $this->mustBeString('url', $input->getArgument('url'));

        $table = new Table($output);
        $table
            ->setHeaders(['partnerId', 'userId', 'hostId', 'url'])
            ->setRows([
                [$partnerId, $userId, $hostId, $url],
            ]);
        $table->render();

        $versionList = [
            'H003' => Version::v24(),
            'H004' => Version::v25(),
            'H005' => Version::v30(),
        ];
        $helper      = $this->getHelper('question');
        $question    = new ChoiceQuestion(
            'Please select protocol version',
            // choices can also be PHP objects that implement __toString() method
            array_keys($versionList),
            0
        );

        $versionSelected = $versionList[$helper->ask($input, $output, $question)];

        $bank                = new BankInfo($hostId, $url, $versionSelected, $partnerId, $userId);
        $keyRing             = new KeyRing('myPassword');
        $x509OptionGenerator = new DefaultX509OptionGenerator();

        $keyring = (new INICommand())->__invoke($bank, $keyRing, $x509OptionGenerator);
        $keyring = (new HIACommand())->__invoke($bank, $keyRing, $x509OptionGenerator);
        $keyring = (new HPBCommand())->__invoke($bank, $keyRing);

        $table = new Table($output);
        $table
            ->setHeaders([
                'Hash ' . $keyring->getUserCertificateA()->getCertificatType()->toString() . ' (' . $keyring->getUserCertificateA()->getCertificatType()->getHash() . ')',
                'Hash ' . $keyring->getUserCertificateE()->getCertificatType()->toString() . ' (' . $keyring->getUserCertificateE()->getCertificatType()->getHash() . ')',
                'Hash ' . $keyring->getUserCertificateX()->getCertificatType()->toString() . ' (' . $keyring->getUserCertificateX()->getCertificatType()->getHash() . ')',
            ])
            ->setRows([
                [
                    nl2br($keyring->getUserCertificateA()->getCertificatX509()->digest()),
                    nl2br($keyring->getUserCertificateE()->getCertificatX509()->digest()),
                    nl2br($keyring->getUserCertificateX()->getCertificatX509()->digest()),

                ],
            ]);
        $table->render();

        return Command::SUCCESS;
    }

    /** @param string|string[]|null $value */
    private function mustBeString(string $name, $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException($name . ' not a string');
    }
}

$application = new Application('Ebics tester', '1.0.0');
$command     = new DefaultCommand();

$application->add($command);

$application->setDefaultCommand((string) $command->getName(), true);
$application->run();
