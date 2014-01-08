<?php 

/**
 * Part of the Deeployer package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * Part of the code used in this package came from https://github.com/lkwdwrd/git-deploy
 * 
 * @package    Deeployer
 * @version    1.0.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

namespace PragmaRX\Deeployer\Deployers;

use PragmaRX\Deeployer\Support\Config;
use PragmaRX\Deeployer\Support\Git;
use PragmaRX\Deeployer\Support\Composer;
use PragmaRX\Deeployer\Support\Artisan;
use PragmaRX\Deeployer\Support\Remote;

use Illuminate\Log\Writer;

abstract class Deployer implements DeployerInterface {

    private $payload;

    private $config;

    private $log;

    private $git;

    private $composer;

    private $artisan;

    private $messages;

    public function __construct(Config $config, Writer $log, Git $git, Composer $composer, Artisan $artisan)
    {
        $this->config = $config;

        $this->log = $log;

        $this->git = $git;

        $this->composer = $composer;

        $this->artisan = $artisan;
    }

    public function deploy($payload)
    {
        $this->payload = $payload;

        return $this->execute();
    }

    protected function execute()
    {
        $repository = $this->payload->repository->url;

        $branch = basename( $this->payload->ref );
 
        foreach($this->config->get('projects') as $project)
        {
            if ($project['repository'] == $repository && $project['branch'] == $branch)
            {
                $this->message(sprintf(
                                        'deploying repository: %s branch: %s', 
                                        $project['repository'], 
                                        $project['branch']
                                     )
                                );

                $this->executeAll($project);
            }
        }
    }

    public function executeAll($project)
    {
        $this->runGit($project);

        $this->runComposer($project);

        $this->runArtisan($project);
    }

    protected function runGit($project)
    {
        $this->git->setConnection($project['ssh_connection']);

        $this->git->setDirectory($project['directory']);

        $this->message('executing git pull...');

        $this->git->pull($project['remote'], $project['branch']);

        $this->logMessages($this->git->getMessages());
    }

    protected function runComposer($project)
    {
        $this->composer->setConnection($project['ssh_connection']);

        $this->composer->setDirectory($project['directory']);

        if ($project['composer_update'])
        {
            $this->message('executing composer update...');
            $this->composer->update();
        }

        if ($project['composer_optimize'])
        {
            $this->message('executing composer dump-autoload --optimize...');
            $this->composer->dumpOptimized(); 
        }

        $this->logMessages($this->composer->getMessages());
    }

    protected function runArtisan($project)
    {
        if ( ! $project['artisan_migrate'])
        {
            return false;
        }

        $this->artisan->setConnection($project['ssh_connection']);

        $this->artisan->setDirectory($project['directory']);

        $this->message('executing artisan migrate...');

        $this->artisan->migrate();

        $this->logMessages($this->artisan->getMessages());
    }

    public function getMessages()
    {
        return $this->messages;
    }

    private function logMessages($messages)
    {
        foreach($messages as $message)
        {
            $this->message($message);
        }
    }

    private function message($message)
    {
        $this->log->info('Deeployer: '.$message);
    }

}