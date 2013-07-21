<?php

namespace Wrep\IDealBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();

		$treeBuilder->root('idealbundle')
			->children()
				->arrayNode('merchant')
				->addDefaultsIfNotSet()
					->children()
						->scalarNode('id')->defaultValue(null)->end()
						->scalarNode('subid')->defaultValue(0)->end()
						->scalarNode('certificatePath')->defaultValue(null)->end()
						->scalarNode('certificatePassphrase')->defaultValue(null)->end()
					->end()
				->end()
				->arrayNode('acquirer')
				->addDefaultsIfNotSet()
					->children()
						->scalarNode('url')->defaultValue(null)->end()
						->scalarNode('certificatePath')->defaultValue(null)->end()
					->end()
				->end()
				->arrayNode('client')
				->addDefaultsIfNotSet()
					->children()
						->scalarNode('timeout')->defaultValue(15)->end()
					->end()
				->end()
			->end();

		return $treeBuilder;
	}
}
