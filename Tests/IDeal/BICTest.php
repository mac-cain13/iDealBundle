<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use Wrep\IDealBundle\IDeal\BIC;
use Wrep\IDealBundle\Exception\InvalidArgumentException;

class BICTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validConstructorData
	 */
	public function testConstruction($code)
	{
		$bic = new BIC($code);

		$this->assertInstanceOf('Wrep\IDealBundle\IDeal\BIC', $bic);
	}

	public function validConstructorData()
	{
		return array(
			array('ABNANL2A'),
			array('abnanl2a'),
			array('ABNANL2R'),
			array('AEGONL2U'),
			array('CEBUNL2U'),
			array('CITINL2X'),
			array('DSSBNL22'),
			array('FVLBNL22'),
			array('FTSBNL2R'),
			array('FRBKNL2L'),
			array('INGBNL2A'),
			array('RABONL2U'),
			array('SNSBNL2A'),
			array('GEBABEBB36A'),
			array('gebabebb36a'),
			);
	}

	/**
	 * @dataProvider invalidConstructorData
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidConstruction($code)
	{
		new BIC($code);
	}

	public function invalidConstructorData()
	{
		return array(
			array(null),
			array( array(1 => 'foo') ),
			array( new \StdClass() ),

			array('BNANL2A'),
			array('ABNANL2A1'),
			array('ABNANL2A12'),
			array('ABNANL2A1234'),

			array('1234NL2A'),
			array('1234NL2A123'),

			array('ABNA122A'),
			array('ABNA122A123'),
			array('@BNANL2A'),
			);
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetCode($code)
	{
		$bic = new BIC($code);

		$this->assertEquals( strtoupper($code), $bic->getCode() );
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testToString($code)
	{
		$bic = new BIC($code);

		$this->assertEquals(strtoupper($code), (string)$bic);
	}
}
