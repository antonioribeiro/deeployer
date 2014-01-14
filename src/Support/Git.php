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

use GitWrapper\GitWrapper;

class Git extends Remote {

    public function pull($remote, $branch, $force = false)
    {
        if( ! $force)
        {
        	// Do a normal git pull origin master
        	$this->command("git pull $remote $branch");	
        }
        else
        {
        	// There is not really a git pull --force, it's not safe, so...
        	// 
        	// Fetch branch to FETCH_HEAD.
        	$this->command("git fetch $remote $branch");		

        	// Force state to FETCH_HEAD, all changes to files are lost here.
        	$this->command("git reset --hard FETCH_HEAD");	
        }
    }
    
}
