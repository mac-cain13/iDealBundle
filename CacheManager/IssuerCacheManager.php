<?php

namespace Wrep\IDealBundle\CacheManager;

use Wrep\IDealBundle\IDeal\Client;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class IssuerCacheManager extends CacheWarmer implements CacheClearerInterface
{
	const CACHE_PATH = '/wrep/idealbundle/issuer.cache.php';

	private $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Warms up the cache.
	 *
	 * @param string $cacheDir The cache directory
	 */
	public function warmUp($cacheDir)
	{
		serialize($issuerList);

		$this->writeCacheFile($cacheDir . self::CACHE_PATH, $cacheContent);
	}

	/**
     * Checks whether this warmer is optional or not.
     *
     * Optional warmers can be ignored on certain conditions.
     *
     * A warmer should return true if the cache can be
     * generated incrementally and on-demand.
     *
     * @return Boolean true if the warmer is optional, false otherwise
     */
    public function isOptional()
    {
    	return true;
    }

	/**
	 * Clears any caches necessary.
	 *
	 * @param string $cacheDir The cache directory
	 */
	public function clear($cacheDir)
	{
		@unlink($cacheDir . self::CACHE_PATH);
	}
}