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

use PragmaRX\Support\Config;

use PragmaRX\Deeployer\Support\Git;
use PragmaRX\Deeployer\Support\Composer;
use PragmaRX\Deeployer\Support\Artisan;
use PragmaRX\Deeployer\Support\Remote;
use PragmaRX\Deeployer\Support\Process;

use Illuminate\Log\Writer;
use Illuminate\Foundation\Application;

abstract class Deployer implements DeployerInterface {

    protected $payload;

    private $app;

    private $config;

    private $log;

    private $git;

    private $composer;

    private $artisan;

    private $remote;

    private $messages = array();

    private $envoyPath;

    public function __construct(
                                    Application $app, 
                                    Config $config, 
                                    Writer $log, 
                                    Git $git, 
                                    Composer $composer, 
                                    Artisan $artisan,
                                    Remote $remote
                                )
    {
        $this->app = $app;

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
            $project = $this->checkAttributes($project);

            if ($this->repositoriesAreTheSame($project['git_repository']) && $project['git_branch'] == $this->getBranch())
            {
                $this->message(sprintf(
                                        'deploying found repository: %s branch: %s',
                                        $project['git_repository'], 
                                        $project['git_branch']
                                     )
                                );

                $this->executeAll($project);

                $found++;
            }
        }

        if($this->config->get('envoy_pass_through'))
        {
            $task = sprintf('%s:%s', $this->getRepositoryUrl(), $this->getBranch());

            $this->message('Executing Envoy pass-through: '.$task);

            $this->envoyRunTask($this->config->get('envoy_user'), $task);
        }
        else
        if ($found === 0)
        {
            $this->message('No repositories found. Please check the repository and branch names.');
        }
    }
 
    private function checkAttributes($project)
    {
        if(isset($project['git_repository'])) 
        {
            $project['git_repository'] = removeTrailingSlash($project['git_repository']);  
        } 

        return $project;
    }

    public function executeAll($project)
    {
        if (isset($project['ssh_connection']))
        {
            $this->runGit($project);

            $this->runComposer($project);

            $this->runArtisan($project);

            $this->runPostDeployCommands($project);
        }

        $this->runEnvoyTasks($project);
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

            $this->iniSetConfig('max_execution_time', 'composer_timeout');

            $this->composer->update(
                                        $project['composer_optimize_autoload'], 
                                        $project['composer_extra_options']
                                    );

            $this->iniRestoreConfig('max_execution_time');
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

    protected function runEnvoyTasks($project)
    {
        if ( ! isset($project['envoy_tasks']))
        {
            return;
        }

        foreach($project['envoy_tasks'] as $task)
        {
            if ( ! $this->envoyRunTask($project['envoy_user'] ?: $this->config->get('envoy_user'), $task))
            {
                break;
            }
        }
    }

    private function envoyIsAvailable()
    {
        $path = $this->config->get('envoy_executable_path');

        if ($path == 'automatic' || empty($path))
        {
            $this->envoyPath = getExecutablePath('envoy');
        }
        else
        {
            $this->envoyPath = $path;
        }

        if ( ! empty($this->envoyPath))
        {
            if ( ! is_executable($this->envoyPath))
            {
                $this->message("Envoy file ($this->envoyPath) is not executable.");

                $this->envoyPath = '';
            }
            else
            {
                $this->message('Envoy executable found at: '.$this->envoyPath);
            }
        }
        else
        {
            $this->message('Envoy was not found.');
        }

        return ! empty($this->envoyPath);
    }

    private function envoyRunTask($user, $task)
    {
        if ( ! $this->envoyIsAvailable())
        {
            return;
        }

        $this->message('Running Envoy task '.$task);

        $process = $this->runProcess($user, $task);

        if ( ! $process->isSuccessful()) {
            $this->message('Error executing Laravel Envoy: '.$process->getErrorOutput());

            return false;
        }        

        return true;
    }

    private function runProcess($user, $task) 
    {
        $process = new Process($this->createEnvoyCommand($user)." run $task");

        $process->setTimeout($this->config->get('envoy_timeout'));

        $process->setWorkingDirectory($this->app->make('path.base'));

        $process->run();

        return $process;
    }

    private function createEnvoyCommand($user)
    {
        $command = $this->envoyPath;

        if ( ! empty($user))
        {
            $command = sprintf('sudo -u %s %s', $user, $command);
        }

        return $command;
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

    private function repositoriesAreTheSame($repository)
    {
        return removeTrailingSlash($repository) == $this->getRepositoryUrl();
    }

    private function iniSetConfig($phpKey, $configKey)
    {
        $this->iniConfig[$phpKey] = ini_get($phpKey);

        ini_set($phpKey, $this->config->get($configKey));
    }

    public function iniRestoreConfig($phpKey)
    {
        ini_set($phpKey, $this->iniConfig[$phpKey]);
    }

}