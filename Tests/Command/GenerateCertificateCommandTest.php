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
        echo sys_get_temp_dir();
        $commandTester->execute( array('command' => $command->getName(), 'path' => tempnam(sys_get_temp_dir(), 'phpunit_')) );

        $this->assertRegExp('/.../', $commandTester->getDisplay());

        // ...
    }
}