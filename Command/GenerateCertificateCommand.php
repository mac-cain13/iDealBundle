<?php

namespace Wrep\IDealBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

class GenerateCertificateCommand extends ContainerAwareCommand
{
	private $country;
	private $stateOrProvince;
	private $locality;
	private $organization;
	private $organizationalUnit;

	private $passphrase;

	protected function configure()
	{
		$this->setName('ideal:certificate:generate')
			->setDescription('Generate a self-signed iDeal merchant certificate')
			->setDefinition(array(
				new InputArgument(
					'path',
					InputArgument::OPTIONAL,
					'Folder to put the generated certificate and key in'
				)
			));
	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelper('dialog');

		$this->country = $dialog->ask($output, 'Country:', 'NL');
		$this->stateOrProvince = $dialog->ask($output, 'State or province:', 'Overijssel', array('Friesland', 'Groningen', 'Drenthe', 'Overijssel', 'Gelderland', 'Noord-Brabant', 'Limburg', 'Zeeland', 'Zuid-Holland', 'Noord-Holland'));
		$this->locality = $dialog->ask($output, 'Locality:', 'Zwolle');
		$this->organization = $dialog->ask($output, 'Organization:', 'Wrep');
		$this->organizationalUnit = $dialog->ask($output, 'Organizational unit:', 'iDeal Integration Team');

		$this->passphrase = $dialog->askHiddenResponse($output, 'Passphrase:', true);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Generate private key
		$output->write('Generating private key...');

		$privatekey = openssl_pkey_new(array(
				'digest_alg' => 'aes128',
				'private_key_bits' => 2048,
				'private_key_type' => OPENSSL_KEYTYPE_RSA
			));
		openssl_pkey_export($privatekey, $pemPrivatekey, $this->passphrase);

		$output->writeln(' done');

		// Generate X.509 certificate
		$output->write('Generating X.509 certificate...');

		$csr = openssl_csr_new(array(
			'countryName' 				=> $this->country,
			'stateOrProvinceName' 		=> $this->stateOrProvince,
			'localityName' 				=> $this->locality,
			'organizationName' 			=> $this->organization,
			'organizationalUnitName' 	=> $this->organizationalUnit
		), $privatekey);
		$certificate = openssl_csr_sign($csr, null, $privatekey, 1825);
		openssl_x509_export($certificate, $x509);

		$output->writeln(' done');

		// Figure out the destination folder
		$output->write('Exporting iDeal certificate and private key...');

		$path = getcwd();
		if ( $input->hasArgument('path') ) {
			$path = $input->getArgument('path');
		}

		// Put the certificate on disk
		file_put_contents($path . '/ideal.cer', $x509);
		file_put_contents($path . '/ideal.key', $pemPrivatekey);
		$output->writeln(' done');
	}
}