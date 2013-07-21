<?php

namespace Wrep\IDealBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class IDealExtension extends Extension
{
	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.xml');

		if (isset($config['idealbundle']) && isset($config['idealbundle']['merchant']))
		{
			$container->setParameter('idealbundle.merchant.id', $config['idealbundle']['merchant']['id']);
			$container->setParameter('idealbundle.merchant.subid', $config['idealbundle']['merchant']['subid']);
			$container->setParameter('idealbundle.merchant.certificatePath', $config['idealbundle']['merchant']['certificatePath']);
			$container->setParameter('idealbundle.merchant.certificatePassphrase', $config['idealbundle']['merchant']['certificatePassphrase']);
		}

		if (isset($config['idealbundle']) && isset($config['idealbundle']['acquirer']))
		{
			$container->setParameter('idealbundle.acquirer.url', $config['idealbundle']['acquirer']['url']);
			$container->setParameter('idealbundle.acquirer.certificatePath', $config['idealbundle']['acquirer']['certificatePath']);
		}

		if (isset($config['idealbundle']) && isset($config['idealbundle']['client']))
		{
			$container->setParameter('idealbundle.client.timeout', $config['idealbundle']['client']['timeout']);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getXsdValidationBasePath()
	{
		return __DIR__ . '/../Resources/config/schema';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNamespace()
	{
		return 'http://www.wrep.nl/schema/dic/ideal_bundle';
	}
}
