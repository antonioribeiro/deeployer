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

class Composer extends Remote {

    public function update($optimize = false, $extra = '')
    {
        $optimize = $optimize ? ' --optimize-autoloader ' : '';

        $this->command("composer update $optimize $extra");
    }

    public function dumpAutoload($extra = '')
    {
        $this->command("composer dump-autoload $extra");
    }

    public function dumpOptimized($extra = '')
    {
        $this->dumpAutoload("--optimize $extra");
    }

}
