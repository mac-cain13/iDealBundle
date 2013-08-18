<?php

namespace Wrep\IDealBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Wrep\IDealBundle\Command\GenerateCertificateCommand;

class GenerateCertificateCommandTest extends \PHPUnit_Framework_TestCase
{
	public function testExecute()
	{
		$kernel = $this->getMock('Symfony\Component\HttpKernel\Kernel', array(), array('prod', false));

		$application = new Application($kernel);
		$application->add( new GenerateCertificateCommand() );

		$command = $application->find('ideal:certificate:generate');
		$commandTester = new CommandTester($command);

		$dialog = $command->getHelper('dialog');
		$dialog->setInputStream($this->getInputStream("NL\nFriesland\nSneek\nVoorbeeld Bedrijf\npassword\n\n"));

		$outputPath = sys_get_temp_dir();
		$commandTester->execute( array('command' => $command->getName(), 'path' => $outputPath) );

		$this->assertRegExp('/Exporting iDeal certificate and private key... done/', $commandTester->getDisplay());
		$this->assertRegExp('/-----BEGIN CERTIFICATE-----.*-----END CERTIFICATE-----/s', file_get_contents($outputPath . '/ideal_certificate.cer') );
		$this->assertRegExp('/-----BEGIN .*?PRIVATE KEY-----.*-----END .*?PRIVATE KEY-----/s', file_get_contents($outputPath . '/ideal_key.key') );
		$this->assertRegExp('/-----BEGIN CERTIFICATE-----.*-----END CERTIFICATE-----.*-----BEGIN RSA PRIVATE KEY-----.*-----END RSA PRIVATE KEY-----/s', file_get_contents($outputPath . '/ideal_combined.pem') );
	}

	protected function getInputStream($input)
	{
		$stream = fopen('php://memory', 'r+', false);
		fputs($stream, $input);
		rewind($stream);

		return $stream;
	}
}