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

use Illuminate\Remote\RemoteManager as IlluminateRemote;

class Remote extends IlluminateRemote {
	
    public $messages = array();

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function command($command)
    {
    	$this->into($this->connection)->run(array(
				    'cd '.$this->directory,
				    $command,
				), function($line)
                    {
                        $this->messages[] = $line;
                    });
    }

    public function getMessages()
    {
        return $this->messages;
    }

}