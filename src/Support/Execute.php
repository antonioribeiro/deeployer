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
 * @package    Deeployer
 * @version    1.0.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */
 
namespace PragmaRX\Deeployer\Support;

use Illuminate\Foundation\Composer as IlluminateComposer;

abstract class Execute extends IlluminateComposer {

    protected $environment = array();

    protected $process;

    protected $error;

    private function findSoftware() {}

    public function update($extra = '')
    {
        return $this->command('update', $extra);
    }

    public function command($command, $extra = '')
    {
        $this->newProcess();

        $this->configureEnvironment();

        $this->process->setCommandLine(trim($this->findSoftware()." $command ".$extra));

        $this->process->run();

        if( !$this->process->isSuccessful())
        {
            $this->setError($this->process->getErrorOutput());

            return false;
        }

        return true;
    }

    public function newProcess()
    {
        $this->process = $this->getProcess();
    }

    public function setEnvVar($variable, $value)
    {
        $this->environment[$variable] = $value;
    }

    private function configureEnvironment()
    {
        $env = $this->process->getEnv();

        foreach ($this->environment as $key => $value) {
            $env[$key] = $value;
        }

        $this->process->setEnv($env);
    }

    private function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
    
}
