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

	protected function configure()
	{
		$this->setName('ideal:certificate:generate')
			->setDescription('Generate a self-signed iDeal merchant certificate')
			->setDefinition(array(
				new InputArgument(
					'path',
					InputArgument::REQUIRED,
					'Location to put the generated certificate in'
				)
			));
	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelper('dialog');

		$this->country = $dialog->ask($output, 'Country', 'NL');
		$this->stateOrProvince = $dialog->ask($output, 'State or province', 'Overijssel', array('Friesland', 'Groningen', 'Drenthe', 'Overijssel', 'Gelderland', 'Noord-Brabant', 'Limburg', 'Zeeland', 'Zuid-Holland', 'Noord-Holland'));
		$this->locality = $dialog->ask($output, 'Locality', 'Zwolle');
		$this->organization = $dialog->ask($output, 'Organization', 'Wrep');
		$this->organizationalUnit = $dialog->ask($output, 'Organizational unit', 'iDeal Integration Team');
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
		$passphrase = rtrim(base64_encode(openssl_random_pseudo_bytes(mt_rand(9, 15))), '=');
		openssl_pkey_export($privatekey, $pemPrivatekey, $passphrase);

		$output->writeln(' done');

		// Generate X.509 certificate
		$output->write('Generating X.509 certificate...');

		$csr = openssl_csr_new(array(
			"countryName" 				=> $this->country,
			"stateOrProvinceName" 		=> $this->stateOrProvince,
			"localityName" 				=> $this->locality,
			"organizationName" 			=> $this->organization,
			"organizationalUnitName" 	=> $this->organizationalUnit
		), $privatekey);
		$certificate = openssl_csr_sign($csr, null, $privatekey, 1825);

		openssl_x509_export($certificate, $x509);

		$output->writeln(' done');

		// Put the certificate on disk
		$output->write('Exporting iDeal certificate including private key...');
		file_put_contents($input->getArgument('path'), $x509 . "\n" . $pemPrivatekey);
		$output->writeln(' done');

		// And tell what passphrase we used
		$output->writeln('Used passphrase: ' . $passphrase);
	}
}