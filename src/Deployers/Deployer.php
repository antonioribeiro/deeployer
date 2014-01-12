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

    protected $payload;

    private $config;

    private $log;

    private $git;

    private $composer;

    private $artisan;

    private $remote;

    private $messages = array();

    public function __construct(
                                    Config $config, 
                                    Writer $log, 
                                    Git $git, 
                                    Composer $composer, 
                                    Artisan $artisan,
                                    Remote $remote
                                )
    {
        $this->config = $config;

        $this->log = $log;

        $this->git = $git;

        $this->composer = $composer;

        $this->artisan = $artisan;

        $this->remote = $remote;
    }

    public function deploy($payload)
    {
        $this->payload = $payload;

        return $this->execute();
    }

    protected function execute()
    {
        $repository = $this->getRepositoryUrl();

        $this->message(sprintf('POST received for "%s" branch "%s"', $this->getRepositoryUrl(), $this->getBranch()));

        $found = 0;

        foreach($this->config->get('projects') as $project)
        {
            if ($this->repositoryEquals($project['git_repository']) && $project['git_branch'] == $this->getBranch())
            {
                $this->message(sprintf(
                                        'deploying repository: %s branch: %s', 
                                        $project['git_repository'], 
                                        $project['git_branch']
                                     )
                                );

                $this->executeAll($project);

                $found++;
            }
        }

        if ($found === 0)
        {
            $this->message('No repositories found. Please check the repository and branch names.');
        }
    }
 
    public function executeAll($project)
    {
        $this->runGit($project);

        $this->runComposer($project);

        $this->runArtisan($project);

        $this->runPostDeployCommands($project);
    }

    protected function runGit($project)
    {
        $this->git->setConnection($project['ssh_connection']);

        $this->git->setDirectory($project['remote_directory']);

        $this->message('executing git pull...');

        $this->git->pull($project['git_remote'], $project['git_branch']);

        $this->logMessages($this->git->getMessages());
    }

    protected function runComposer($project)
    {
        $this->composer->setConnection($project['ssh_connection']);

        $this->composer->setDirectory($project['remote_directory']);

        if ($project['composer_update'])
        {
            $this->message('executing composer update...');

            $this->composer->update(
                                        $project['composer_optimize_autoload'], 
                                        $project['composer_extra_options']
                                    );
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

        $this->artisan->setDirectory($project['remote_directory']);

        $this->message('executing artisan migrate...');

        $this->artisan->migrate();

        $this->logMessages($this->artisan->getMessages());
    }

    protected function runPostDeployCommands($project)
    {
        $this->remote->setConnection($project['ssh_connection']);

        $this->remote->setDirectory($project['remote_directory']);

        foreach($project['post_deploy_commands'] as $command)
        {
            $this->message('executing post deploy command: '.$command);

            $this->remote->command($command);
        }

        $this->logMessages($this->remote->getMessages());
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
        $this->log->info(sprintf('Deeployer - %s: %s', $this->getServiceName(), $message));
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    private function repositoryEquals($repository)
    {
        $repository .= (substr($repository, -1) == '/' ? '' : '/');

        return $repository == $this->getRepositoryUrl();
    }
}