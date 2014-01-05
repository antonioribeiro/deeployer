<?php namespace PragmaRX\Deeployer\Vendor\Laravel;

use PragmaRX\Deeployer\Deeployer;

use PragmaRX\Deeployer\Support\Config;
use PragmaRX\Deeployer\Support\Filesystem;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Illuminate\Foundation\AliasLoader as IlluminateAliasLoader;

class ServiceProvider extends IlluminateServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('pragmarx/deeployer', 'pragmarx/deeployer', __DIR__.'/../../../..');

		if( $this->getConfig('create_deeployer_alias') )
		{
			IlluminateAliasLoader::getInstance()->alias(
															$this->getConfig('deeployer_alias'), 
															'PragmaRX\Deeployer\Vendor\Laravel\Facade'
														);
		}	
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerFileSystem();

		$this->registerConfig();

		$this->registerDeeployer();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

	/**
	 * Register the Filesystem driver used by Deeployer
	 * 
	 * @return void
	 */
	private function registerFileSystem()
	{
		$this->app['deeployer.fileSystem'] = $this->app->share(function($app)
		{
			return new Filesystem;
		});
	}

	/**
	 * Register the Config driver used by Deeployer
	 * 
	 * @return void
	 */
	private function registerConfig()
	{
		$this->app['deeployer.config'] = $this->app->share(function($app)
		{
			return new Config($app['deeployer.fileSystem'], $app);
		});
	}

	/**
	 * Takes all the components of Deeployer and glues them
	 * together to create Deeployer.
	 *
	 * @return void
	 */
	private function registerDeeployer()
	{
		$this->app['deeployer'] = $this->app->share(function($app)
		{
			$app['deeployer.loaded'] = true;

			return new Deeployer($app['deeployer.config'], $app['request']);
		});
	}

	/**
	 * Helper function to ease the use of configurations
	 * 
	 * @param  string $key configuration key
	 * @return string      configuration value
	 */
	public function getConfig($key)
	{
		return $this->app['config']["pragmarx/deeployer::$key"];
	}
}
