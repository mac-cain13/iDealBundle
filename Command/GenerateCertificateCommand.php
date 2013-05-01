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
		$this->getHelper('dialog')->ask($output, 'Country', 'NL');
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
			"countryName" => "NL",
			"stateOrProvinceName" => "Overijssel",
			"localityName" => "Zwolle",
			"organizationName" => "Wrep",
			"organizationalUnitName" => "iDeal Integration Team"
		), $privatekey);
		$certificate = openssl_csr_sign($csr, null, $privatekey, 1825);

		openssl_x509_export($certificate, $x509);

		$output->writeln(' done');

		$output->write('Exporting iDeal certificate including private key...');
		file_put_contents($input->getArgument('path'), $x509 . "\n" . $pemPrivatekey);
		$output->writeln(' done');

		$output->writeln('Used passphrase: ' . $passphrase);
	}
}