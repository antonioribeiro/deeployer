<?php namespace PragmaRX\Deeployer\Vendor\Laravel;
 
use PragmaRX\Deeployer\Deeployer;

use PragmaRX\Support\ServiceProvider as PragmaRXServiceProvider;

use PragmaRX\Deeployer\Support\Remote;
use PragmaRX\Deeployer\Support\Composer;
use PragmaRX\Deeployer\Support\Artisan;
use PragmaRX\Deeployer\Support\Git;

use PragmaRX\Deeployer\Deployers\Github;
use PragmaRX\Deeployer\Deployers\Bitbucket;

class ServiceProvider extends PragmaRXServiceProvider {

    protected $packageVendor = 'pragmarx';
    protected $packageVendorCapitalized = 'PragmaRX';

    protected $packageName = 'deeployer';
    protected $packageNameCapitalized = 'Deeployer';

    /**
     * This is the boot method for this ServiceProvider
     *
     * @return void
     */
    public function wakeUp()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->preRegister();

        $this->registerRemote();

        $this->registerGit();

        $this->registerComposer();

        $this->registerArtisan();

        $this->registerGithub();

        $this->registerBitbucket();

        $this->registerDeeployer();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('deeployer');
    }

    /**
     * Register the Composer driver
     * 
     * @return void 
     */
    private function registerComposer()
    {
        $this->app['deeployer.composer'] = $this->app->share(function($app)
        {
            return new Composer($app);
        });
    }

    /**
     * Register the Remote driver
     * 
     * @return void
     */
    private function registerRemote()
    {
        $this->app['deeployer.remote'] = $this->app->share(function($app)
        {
            return new Remote($app);
        });
    }

    /**
     * Register the Artisan driver
     * 
     * @return void
     */
    private function registerArtisan()
    {
        $this->app['deeployer.artisan'] = $this->app->share(function($app)
        {
            return new Artisan($app);
        });
    }

    /**
     * Register the Git driver
     * 
     * @return void
     */
    private function registerGit()
    {
        $this->app['deeployer.git'] = $this->app->share(function($app)
        {
            return new Git($app);
        });
    }

    /**
     * Register the Github driver
     * 
     * @return void
     */
    private function registerGithub()
    {
        $this->app['deeployer.github'] = $this->app->share(function($app)
        {
            return new Github(
                                $app['deeployer.config'], 
                                $app['log'], 
                                $app['deeployer.git'], 
                                $app['deeployer.composer'],
                                $app['deeployer.artisan'],
                                $app['deeployer.remote']
                            );
        });
    }

    /**
     * Register the Bitbucket driver
     * 
     * @return void
     */
    private function registerBitbucket()
    {
        $this->app['deeployer.bitbucket'] = $this->app->share(function($app)
        {
            return new Bitbucket(
                                    $app['deeployer.config'], 
	                                $app['log'], 
                                    $app['deeployer.git'],
                                    $app['deeployer.composer'],
                                    $app['deeployer.artisan'],
                                    $app['deeployer.remote']
                                );
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

            return new Deeployer(
                                    $app['deeployer.config'], 
                                    $app['log'],
                                    $app['request'],
                                    $app['deeployer.github'], 
                                    $app['deeployer.bitbucket']
                                );
        });
    }

    /**
     * Get the root directory for this ServiceProvider
     * 
     * @return string
     */
    public function getRootDirectory()
    {
        return __DIR__.'/../..';
    }    
}
