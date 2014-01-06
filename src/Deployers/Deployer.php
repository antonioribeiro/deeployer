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
use PragmaRX\Deeployer\Support\Composer;
use PragmaRX\Deeployer\Support\Git;

abstract class Deployer implements DeployerInterface {

	private $payload;

	private $config;

	public function __construct(Config $config, Git $git, Composer $composer)
	{
		$this->config = $config;

		$this->composer = $composer;

		$this->git = $git;
	}

	public function deploy($payload)
	{
		$this->payload = $payload;

	    return $this->execute();
	}

	protected function execute()
	{
	    $repository = $this->payload->repository->name;

	    $branch = basename( $this->payload->ref );
 
	    foreach($this->config->get('projects') as $project)
	    {
	    	if ($project['repository'] == $repository && $project['branch'] == $branch)
	    	{
	    		$this->updateRepository($project);
	    	}
	    }
	}

	public function updateRepository($project)
	{
		$this->pull($project);

		$this->composerUpdate($project);
	}

	protected function pull($project)
	{
		 // $this->git->pull()
	}

	protected function composerUpdate($project)
	{
		if ( ! $project['update_composer'])
		{
			return false;
		}

		$this->composer->setWorkingPath($project['directory']);

		$this->composer->update();

        if ($project['composer_dump_autoload'])
        {
            if ($project['composer_optimize'])
            {
                $this->composer->dumpOptimized(); 
            }
            else
            {
                $this->composer->dumpAutoloads(); 
            }
        }
	}
}